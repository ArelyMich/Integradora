<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Secuencia;

class SecuenciaController extends Controller
{
    //
    public function index()
    {
        $secuencias = $this->secuencias();
        return view('secuencias.index',compact('secuencias'));
    }

    public function createView(){


        return view('secuencias.createView');
    }

    private function secuencias(){
        $secuencias = Secuencia::all();
            return $secuencias;
    }

    public function store(){
        
    }
    public function uploadAndExtract(Request $request)
    {
        $request->validate(['caratula_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240']);

        try {
            // 1. Guardar el archivo en el storage temporal
            $path = $request->file('caratula_file')->store('temp/caratulas');
            $fullPath = Storage::path($path);

            // 2. Ejecutar OCR en el archivo
            $text = (new TesseractOCR($fullPath))
                ->lang('spa') // Usar el idioma español
                ->run();

            // 3. Procesar el texto extraído
            $data = $this->parseCaratulaText($text);

            // 4. Eliminar el archivo temporal
            Storage::delete($path);

            // 5. Devolver los datos para autocompletar el formulario
            return response()->json([
                'success' => true,
                'message' => 'Datos extraídos correctamente.',
                'extracted_data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo. ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mapea el texto plano del OCR a un array de datos estructurados.
     * ESTA ES LA PARTE MÁS CRÍTICA Y REQUIERE AJUSTES FINOS.
     */
    /**
     * Mapea el texto plano del OCR a un array de datos estructurados.
     * MEJORADO: Manejo de Competencia como bloque de texto entre dos etiquetas.
     */
    private function parseCaratulaText(string $text): array
    {
        $lines = explode("\n", $text);
        $data = [];
        $rawText = $text; // Mantener el texto completo para extracción por bloques

        // --- Patrones de Extracción Simple (Líneas Clave) ---
        $patterns = [
            'carrera'    => '/Carrera:\s*(.*)/i',
            'asignatura' => '/Asignatura:\s*(.*)/i',
            'docente'    => '/Docente:\s*(.*)/i',
            'tutor'      => '/Tutor\s*\(s\)\s*de\s*grupo:\s*(.*)/i',
            'link'       => '/Link\s*en\s*Internet:\s*(.*)/i',
            // El email es complejo, a veces va pegado al docente, lo buscaremos aparte
            'email'      => '/e-mail\s*:\s*(.*)/i', 
        ];

        // --- Extracción de campos simples y el periodo (tabulado) ---
        foreach ($lines as $line) {
            $line = trim($line);
            
            // 1. Extracción de datos tabulados (Periodo, Año, Cuatrimestre, Grupo)
            // Mejoramos el patrón para ser menos estricto con los espacios y el prefijo (O o A)
            if (preg_match('/(O|A)?\s*(Enero-Abril|Mayo-Agosto|Septiembre-Diciembre)\s*(\d{4})\s*Cuatrimestre:\s*(\d+)\s*Grupo:\s*(\w+)/i', $line, $matches)) {
                 $data['periodo_texto'] = trim($matches[2]); 
                 $data['anio'] = (int)$matches[3];
                 $data['cuatrimestre'] = (int)$matches[4];
                 $data['grupo'] = trim($matches[5]);
            }
            
            // 2. Extracción de campos simples
            foreach ($patterns as $key => $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    // Limpieza básica del valor extraído
                    $data[$key] = trim($matches[1], " \t\n\r\0\x0B:");
                }
            }
        }

        // --- Extracción de la Competencia (Bloque de Texto) ---
        // La Competencia es el texto que va después de "Competencia(s):" y antes de "Período:", "Carrera:", o el siguiente bloque principal.
        
        $startTag = 'Competencia(s):';
        $endTagPattern = '/(Periodo:|Período:|Carrera:|Asignatura:)/i';
        
        if (stripos($rawText, $startTag) !== false) {
            // Obtener el texto que sigue a la etiqueta de inicio
            $startPos = stripos($rawText, $startTag) + strlen($startTag);
            $subText = substr($rawText, $startPos);
            
            // Buscar la posición de la siguiente etiqueta principal (etiqueta de fin)
            if (preg_match($endTagPattern, $subText, $matches, PREG_OFFSET_CAPTURE)) {
                $endPos = $matches[0][1]; // Posición de la etiqueta de fin en $subText
                $competenciaRaw = substr($subText, 0, $endPos);
            } else {
                // Si no encuentra la etiqueta de fin, toma el resto del texto (menos probable en carátula)
                $competenciaRaw = $subText;
            }

            // Limpiar y normalizar la competencia
            $data['competencia'] = trim(preg_replace('/\s\s+/', ' ', $competenciaRaw));
        }
        
        // Mapeo del periodo_texto a ID (simulado)
        $periodoMap = [
            'Enero-Abril' => 1,
            'Mayo-Agosto' => 2,
            'Septiembre-Diciembre' => 3,
        ];
        $data['periodo_id'] = $periodoMap[$data['periodo_texto'] ?? ''] ?? null;

        // Limpiar grupo de caracteres no deseados
        $data['grupo'] = strtoupper(trim($data['grupo'] ?? ''));

        return $data;
    }

}
