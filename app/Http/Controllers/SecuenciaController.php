<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Secuencia;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Str;
use App\Models\Caratula;
use App\Models\Unidad;
use PhpOffice\PhpWord\TemplateProcessor;




class SecuenciaController extends Controller
{
    //
    public function index(Request $request)
{
    $query = Secuencia::with('caratula');

    // 🔎 BUSCADOR
    if ($request->filled('buscar')) {

        $query->whereHas('caratula', function ($q) use ($request) {

            $q->where('asignatura', 'like', '%' . $request->buscar . '%');

        });

    }

    // 📌 FILTRO ESTADO
    if ($request->filled('estado')) {

        $query->where('estatus', $request->estado);

    }

    // 📌 ORDENAMIENTO
    switch ($request->orden) {

        case 'nombre_asc':
            $query->join('caratulas','secuencias.caratula_id','=','caratulas.id')
                  ->orderBy('caratulas.asignatura','asc');
            break;

        case 'nombre_desc':
            $query->join('caratulas','secuencias.caratula_id','=','caratulas.id')
                  ->orderBy('caratulas.asignatura','desc');
            break;

        case 'activo':
            $query->orderBy('estatus','desc');
            break;

        default:
            $query->latest();
    }

    // 📄 PAGINACIÓN
    $secuencias = $query->paginate(10);

    // 📊 RESUMEN
    $total = Secuencia::count();

    $activas = Secuencia::where('estatus','activo')->count();

    $inactivas = Secuencia::where('estatus','borrador')->count();

    return view(
        'secuencias.index',
        compact(
            'secuencias',
            'total',
            'activas',
            'inactivas'
        )
    );
}

    public function createView($id)
    {
        // Cargar secuencia con relaciones
        $secuencia = Secuencia::with([
            'caratula',
            'unidades'
        ])->findOrFail($id);

        // Obtener carátula
        $caratula = $secuencia->caratula;

        // Obtener unidades
        $unidades = $secuencia->unidades;

        return view(
            'secuencias.createView',
            compact(
                'secuencia',
                'caratula',
                'unidades'
            )
        );
    }

    private function secuencias(){
        $secuencias = Secuencia::all();
            return $secuencias;
    }

    public function store(){
        
    }
    public function uploadAndExtract(Request $request)
{
    $request->validate([
        'caratula_file' => 'required|file|mimes:pdf|max:10240'
    ]);

    try {

        // ------------------------------------------------------------
        // 1. Guardar archivo
        // ------------------------------------------------------------

        $uploadedFile = $request->file('caratula_file');

        $filename = time() . '_' . Str::slug(
            pathinfo(
                $uploadedFile->getClientOriginalName(),
                PATHINFO_FILENAME
            )
        ) . '.' . $uploadedFile->getClientOriginalExtension();

        $path = $uploadedFile->storeAs(
            'public/secuencias/pdf',
            $filename
        );

        $fullPath = Storage::path($path);

        $relativePath = "secuencias/pdf/$filename";


        // ------------------------------------------------------------
        // 2. Extraer texto del PDF
        // ------------------------------------------------------------

        try {

            $text = Pdf::getText($fullPath);

            if (empty(trim($text))) {
                throw new \Exception("Spatie returned empty");
            }

        } catch (\Throwable $e) {

            $parser = new Parser();

            $pdf = $parser->parseFile($fullPath);

            $text = $pdf->getText();
        }


        // ------------------------------------------------------------
        // 3. Normalizar texto
        // ------------------------------------------------------------

        $textNormalized = preg_replace(
            "/\r\n|\r|\n+/",
            "\n",
            $text
        );

        $textNormalized = preg_replace(
            '/\s+\n/',
            "\n",
            $textNormalized
        );

        $textNormalized = preg_replace(
            '/\n\s+/',
            "\n",
            $textNormalized
        );


        // DEBUG
        file_put_contents(
            storage_path('app/debug_text.txt'),
            $textNormalized
        );


        // ------------------------------------------------------------
        // 4. EXTRAER PROGRAMA
        // ------------------------------------------------------------

        $programa = $this->extractPrograma($textNormalized);


        // ------------------------------------------------------------
        // 5. EXTRAER UNIDADES
        // ------------------------------------------------------------

        $unidades = $this->extractUnidades($textNormalized);
        

// ------------------------------------------------------------
// CREAR CARATULA
// ------------------------------------------------------------

$caratula = Caratula::create([

    'carrera' => $programa['programa_educativo'] ?? null,

    'docente' => null,

    'cuatrimestre' => $programa['cuatrimestre'] ?? null,

    'periodo_escolar' => null,

    'asignatura' => $programa['asignatura'] ?? null,

    'grupo' => null,

    'competencia' => $programa['competencia'] ?? null,

    'tipo_competencia' =>
        $programa['tipo_compentencia'] ?? null,

    'creditos' => $programa['creditos'] ?? null,

    'modalidad' => $programa['modalidad'] ?? null,

    'horas_saber' =>
        $programa['horas_saber'] ?? null,

    'horas_saber_hacer' =>
        $programa['horas_hcaer'] ?? null,

    'horas_totales' =>
        $programa['horas_totales'] ?? null,

    'horas_semana' =>
        $programa['horas_semana'] ?? null,

    'proposito' =>
        $programa['proposito'] ?? null,

    'json_data' => json_encode($programa)

]);
// ------------------------------------------------------------
// CREAR SECUENCIA
// ------------------------------------------------------------

$secuencia = Secuencia::create([

    'ruta_pdf' => $relativePath,

    'caratula_id' => $caratula->id,

    'docente_id' => null,

    'materia_id' => null,

    'carrera_id' => null,

    'periodo_id' => null,

    'revisor_id' => null,

    'director_id' => null,

    'tutor_id' => null,

    'estatus' => 'borrador',

    'fecha_entrega' => now()

]);
// ------------------------------------------------------------
// CREAR UNIDADES
// ------------------------------------------------------------

foreach ($unidades as $u) {

    Unidad::create([

        'secuencia_id' => $secuencia->id,

        'nombre' => $u['nombre'] ?? null,

        'numero' => $u['numero'] ?? null,

        'horas' => $u['horas_totales'] ?? null

    ]);

}
        // ------------------------------------------------------------
        // 6. Agregar ruta PDF
        // ------------------------------------------------------------

        $programa['ruta_pdf'] = $relativePath;

        


        // ------------------------------------------------------------
        // 7. Respuesta JSON
        // ------------------------------------------------------------
        return redirect()->route(
            'secuencias.edit',
            ['id' => $secuencia->id]
            );

    }

    catch (\Exception $e) {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ], 500);

    }
}

 private function extractPrograma($text)
{
    $out = [];

    // Limpieza básica
    $clean = preg_replace('/[ ]{2,}/', ' ', $text);
    $clean = preg_replace("/\r|\t/", "", $clean);
    $clean = preg_replace("/\n{2,}/", "\n", $clean);


     // ---- PROGRAMA EDUCATIVO (captura el bloque hasta la siguiente sección) ----
     if (preg_match('/PROGRAMA\s+EDUCATIVO:?\s*(.+?)\s*EN\s+COMPETENCIAS\s+PROFESIONALES/si', $clean, $m)) {
        $out['programa_educativo'] = $this->normalizeMultiline($m[1]);
    }


    // ---- ASIGNATURA ----
    if (preg_match('/PROGRAMA DE ASIGNATURA:\s*(.*?)\s*CLAVE/i', $clean, $m))
        $out['asignatura'] = trim($m[1]);

    // ---- CLAVE ----
    if (preg_match('/CLAVE:\s*([A-Z0-9\-]+)/i', $clean, $m))
        $out['clave'] = trim($m[1]);

    // ---- PROPÓSITO (captura el bloque hasta la siguiente sección) ----
    if (preg_match('/Propósito de aprendizaje de la\nAsignatura.*?\n(.+?)\nCompetencia/s', $clean, $m))
        $out['proposito'] = $this->cleanBlock($m[1]);

    // ---- COMPETENCIA ----
    if (preg_match('/Competencia a la que\ncontribuye la asignatura.*?\n(.+?)\nTipo de/s', $clean, $m))
        $out['competencia'] = $this->cleanBlock($m[1]);

    // ---- TIPO COMPETENCIA ----
    if (preg_match('/Tipo de\s+competencia\s*\n.*\n([^\n]+)/i', $clean, $m)) {
        $line = trim($m[1]);
        // Buscar explícitamente una de las tres opciones
        if (preg_match('/\b(Base|Transversal|Específica)\b/i', $line, $m2)) {
            $out['tipo_compentencia'] = $m2[1];
        }
    }


   // Normalizar espacios raros
$clean = preg_replace('/\s+/u', ' ', $clean);

// ---- TABLA NUMÉRICA ----
if (preg_match('/(\d+)\s+(\d+[.,]?\d*)\s+([A-Za-zÁÉÍÓÚáéíóú]+)\s+(\d+)\s+(\d+)/u', $clean, $m)) {
    $out['cuatrimestre']   = (int)$m[1];
    $out['creditos']       = (float)$m[2];
    $out['modalidad']      = $m[3];
    $out['horas_semana']   = (int)$m[4];
    $out['horas_totales']  = (int)$m[5];
}

    // ---- Buscar fila final Totales ----
    if (preg_match('/Totales\s+(\d+)\s+(\d+)\s+(\d+)/i', $text, $m)) {
        $out['horas_saber']   = (int)$m[1];
        $out['horas_hcaer']   = (int)$m[2];
  
    }


    return $out;
}

    
    private function extractUnidades($text)
    {
        $units = [];

        // Captura bloques: "Unidad de Aprendizaje I. Nombre ..."
        preg_match_all('/Unidad de Aprendizaje\s+([IVX]+)\.\s*(.+?)\nPropósito Esperado([\s\S]*?)(?=Unidad de Aprendizaje|$)/i', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) 
        {
            $roman     = trim($m[1]);
            $name      = trim($m[2]);
            $content   = $m[3];

            $unit = [
                'numero'        => $roman,
                'nombre'        => $name,
                'proposito'     => $this->extractBetween($content, 'Propósito esperado', 'Tiempo Asignado'),
                'horas_saber'   => $this->findNumberNear($content, 'Horas del\s*Saber'),
                'horas_hacer'   => $this->findNumberNear($content, 'Horas del\s*Saber Hacer'),
                'horas_totales' => $this->findNumberNear($content, 'Horas Totales'),
                'temas'         => $this->extractTemasFromBlock($content)
            ];

            $units[] = $unit;
        }

        return $units;
    }


    private function extractTemasFromBlock($block)
    {
        $temas = [];

        // detectar tabla "Temas"
        if (preg_match_all('/([A-ZÁÉÍÓÚÑa-z0-9\-\s]+)\nDimensión Conceptual([\s\S]*?)Dimensión\s*Actuacional([\s\S]*?)Dimensión\s*Socioafectiva([\s\S]*?)\n\n/i', $block, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $m)
            {
                $temas[] = [
                    'tema'              => trim($m[1]),
                    'dimension_concep'  => trim($m[2]),
                    'dimension_actu'    => trim($m[3]),
                    'dimension_socio'   => trim($m[4]),
                ];
            }
        }

        return $temas;
    }


    // helpers
    private function normalizeMultiline($text)
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)); // compacta espacios/tabuladores/nuevas líneas
        return trim($text);
    }

    private function cleanBlock($text)
    {
        $text = preg_replace("/\n/", " ", $text);
        $text = preg_replace("/\s{2,}/", " ", $text);
        return trim($text);
    }
    private function sliceNear($text, $needle, $chars=200) {
        $pos = strpos($text, $needle);
        if ($pos === false) return substr($text,0,$chars);
        $start = max(0, $pos-$chars);
        return substr($text, $start, $chars*2);
    }

    private function extractBetween($text, $start, $end) {
        if (preg_match('/'.preg_quote($start,'/').'\s*(.*?)\s*'.preg_quote($end,'/').'/is', $text, $m)) return trim($m[1]);
        return null;
    }

    private function findNumberNear($text, $label) {
        if (preg_match('/'.preg_quote($label,'/').'.{0,30}?(\d+)/i', $text, $m)) return (int)$m[1];
        return null;
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required'
        ]);

        $secuencia = Secuencia::findOrFail($id);

        $secuencia->estatus = $request->estatus;

        $secuencia->save();

        return redirect()
            ->route('secuencias.index')
            ->with('success','Estatus actualizado');
    }

    public function update(Request $request, $id)
    {

    $secuencia = Secuencia::findOrFail($id);

    $caratula = Caratula::findOrFail(
    $secuencia->caratula_id
    );

    //
    // ACTUALIZAR CARÁTULA
    //

    $caratula->update([

    'carrera' => $request->carrera,
    'asignatura' => $request->asignatura,
    'competencia' => $request->competencia,
    'cuatrimestre' => $request->cuatrimestre,

    ]);

    //
    // ACTUALIZAR UNIDADES
    //

    if($request->has('unidades')){

    foreach($request->unidades as $u){

    if(isset($u['id'])){

    // UPDATE existente

    Unidad::where('id',$u['id'])
    ->update([

    'nombre' => $u['titulo'] ?? '',
    'horas' => $u['duracion'] ?? 0

    ]);

    }else{

    // CREAR nueva

    Unidad::create([

    'secuencia_id' => $secuencia->id,
    'nombre' => $u['titulo'] ?? '',
    'numero' => 'NUEVA',
    'horas' => $u['duracion'] ?? 0

    ]);

    }

    }

    }

    return redirect()
    ->route('secuencias.edit',$secuencia->id)
    ->with('success','Secuencia actualizada correctamente');

    }


    // NUEVA
public function create()
{

$caratula = null;
$unidades = [];

return view('secuencias.createView',[
'caratula'=>$caratula,
'unidades'=>$unidades
]);

}






public function exportWord($id)
{
    $secuencia = Secuencia::with([
        'caratula',
        'unidades'
    ])->findOrFail($id);

    $caratula = $secuencia->caratula;
    $unidades = $secuencia->unidades;

    $templatePath = storage_path(
        'app/templates/plantilla_secuencia.docx'
    );

    $template = new TemplateProcessor($templatePath);

    /*
    ===============================
    CARÁTULA
    ===============================
    */

    $template->setValue(
        'carrera',
        $caratula->carrera ?? ''
    );

    $template->setValue(
        'cuatrimestre',
        $caratula->cuatrimestre ?? ''
    );

    $template->setValue(
        'asignatura',
        $caratula->asignatura ?? ''
    );

    $template->setValue(
        'proposito',
        $caratula->proposito ?? ''
    );

    $template->setValue(
        'competencia',
        $caratula->competencia ?? ''
    );

    $template->setValue(
        'tipo_competencia',
        $caratula->tipo_competencia ?? ''
    );

    $template->setValue(
        'creditos',
        $caratula->creditos ?? ''
    );

    $template->setValue(
        'modalidad',
        $caratula->modalidad ?? ''
    );

    $template->setValue(
        'horas_saber',
        $caratula->horas_saber ?? ''
    );

    $template->setValue(
        'horas_saber_hacer',
        $caratula->horas_saber_hacer ?? ''
    );

    $template->setValue(
        'horas_totales',
        $caratula->horas_totales ?? ''
    );

    $template->setValue(
        'horas_semana',
        $caratula->horas_semana ?? ''
    );

    /*
    ===============================
    UNIDADES (VARIABLES)
    ===============================
    */

    $this->fillUnidadBlock($template, $unidades);

    /*
    ===============================
    GENERAR ARCHIVO
    ===============================
    */

    $fileName =
        'secuencia_'.$secuencia->id.'.docx';

    $tempFile =
        storage_path('app/'.$fileName);

    $template->saveAs($tempFile);

    return response()->download(
        $tempFile,
        $fileName,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]
    )->deleteFileAfterSend(true);
}

private function fillUnidadBlock(TemplateProcessor $template, $unidades): void
{
    $mainPart = $this->getTemplateMainPart($template);

    $pattern = '/<w:p\b(?:(?!<w:p\b).)*?\$\{unidad_block\}.*?<\/w:p>(.*?)<w:p\b(?:(?!<w:p\b).)*?\$\{\/unidad_block\}.*?<\/w:p>/s';

    $replacement = '';

    if ($unidades->count() > 0 && preg_match($pattern, $mainPart, $matches)) {
        $block = $matches[1];

        foreach ($unidades as $unidad) {
            $replacement .= str_replace(
                [
                    '${unidad_nombre}',
                    '${unidad_horas}',
                ],
                [
                    $this->escapeWordXmlValue($this->formatUnidadNombre($unidad)),
                    $this->escapeWordXmlValue($unidad->horas ?? ''),
                ],
                $block
            );
        }
    }

    $mainPart = preg_replace_callback(
        $pattern,
        fn () => $replacement,
        $mainPart,
        1
    );

    $this->setTemplateMainPart($template, $mainPart);
}

private function formatUnidadNombre(Unidad $unidad): string
{
    $numero = trim((string) ($unidad->numero ?? ''));
    $nombre = trim((string) ($unidad->nombre ?? ''));

    if ($numero === '') {
        return $nombre;
    }

    if ($nombre === '') {
        return $numero;
    }

    return $numero . '. ' . $nombre;
}

private function escapeWordXmlValue($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

private function getTemplateMainPart(TemplateProcessor $template): string
{
    $property = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
    $property->setAccessible(true);

    return $property->getValue($template);
}

private function setTemplateMainPart(TemplateProcessor $template, string $mainPart): void
{
    $property = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
    $property->setAccessible(true);
    $property->setValue($template, $mainPart);
}









   
}
