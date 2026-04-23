<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictamen de Correcciones</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 4px solid #e67e22;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            color: #2c3e50;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-section {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #555;
            margin: 5px 0;
            padding-left: 10px;
            border-left: 2px solid #f39c12;
        }
        .status-badge {
            display: inline-block;
            background-color: #f39c12;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .comentario-item {
            background-color: #fff8e7;
            border-left: 3px solid #f39c12;
            padding: 12px;
            margin: 10px 0;
            border-radius: 3px;
        }
        .comentario-header {
            font-weight: bold;
            color: #d68910;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .comentario-text {
            color: #555;
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .meta-info {
            background-color: #f0f0f0;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .meta-row {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .motivo-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-wrap;
            color: #333;
            line-height: 1.6;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>⚠️ Dictamen de Correcciones</h1>
            <p>Secuencia Didáctica</p>
        </div>

        <!-- Content -->
        <div class="content">
            
            <p class="greeting">
                Estimado(a) <strong>{{ $nombreDocente }}</strong>,
            </p>

            <p class="greeting">
                Tu secuencia didáctica requiere correcciones. Por favor revisa los comentarios a continuación:
            </p>

            <!-- Información General -->
            <div class="info-box">
                
                <div class="info-section">
                    <div class="info-label">📚 Materia:</div>
                    <div class="info-value">
                        <strong>{{ $secuencia->materia?->nombre ?? 'Sin materia' }}</strong>
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-label">🎓 Carrera:</div>
                    <div class="info-value">
                        {{ $secuencia->carrera?->nombre_carrera ?? 'Sin carrera' }}
                    </div>
                </div>

                <div class="info-section">
                    <div class="info-label">👤 Revisor:</div>
                    <div class="info-value">
                        <strong>
                            {{ trim($revisor->name ?? '') }} 
                            {{ trim($revisor->apellido_paterno ?? '') }} 
                            {{ trim($revisor->apellido_materno ?? '') }}
                        </strong>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #bdc3c7; margin: 15px 0;">

                <div class="info-section">
                    <div class="info-label">📊 Estado del Dictamen:</div>
                    <div style="margin-top: 8px;">
                        <span class="status-badge">{{ ucfirst($secuencia->estatus) }}</span>
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #bdc3c7; margin: 15px 0;">

                <div class="info-section">
                    <div class="info-label">💬 Comentarios Generales del Revisor:</div>
                    <div class="motivo-box">{{ $motivo }}</div>
                </div>

            </div>

            <!-- Comentarios Pendientes -->
            @if($comentariosPendientes->count() > 0)
                <div class="warning-box">
                    <strong>⚠️ Observaciones Pendientes de Resolver:</strong>
                    <p style="margin: 10px 0 0 0; font-size: 13px;">
                        Se encontraron {{ $comentariosPendientes->count() }} comentario(s) que requieren tu atención y corrección.
                    </p>
                </div>

                <div style="margin: 20px 0;">
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">📌 Detalle de Observaciones:</h3>
                    
                    @foreach($comentariosPendientes as $comentario)
                        <div class="comentario-item">
                            <div class="comentario-header">
                                📄 Página {{ $comentario->page }}
                                @if($comentario->titulo)
                                    | {{ $comentario->titulo }}
                                @endif
                            </div>
                            
                            <div class="comentario-text">
                                <strong>Observación:</strong><br>
                                {{ $comentario->comentario }}
                            </div>

                            @if($comentario->info)
                                <div style="margin-top: 8px; color: #666; font-size: 13px;">
                                    <strong>Información adicional:</strong> {{ $comentario->info }}
                                </div>
                            @endif

                            <div style="margin-top: 8px; color: #888; font-size: 12px;">
                                Registrado: {{ $comentario->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Meta Information -->
            <div class="meta-info">
                <div class="meta-row">
                    ⏱️ <strong>Fecha del dictamen:</strong> {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="meta-row">
                    📌 <strong>Período:</strong> {{ $secuencia->periodo?->nombre ?? 'Sin período' }}
                </div>
                <div class="meta-row">
                    📊 <strong>Total de observaciones:</strong> {{ $comentariosPendientes->count() }}
                </div>
            </div>

            <p style="text-align: center; color: #7f8c8d; font-size: 13px; margin-top: 30px;">
                —
            </p>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">Este es un correo automático de la plataforma Integradora.</p>
            <p style="margin: 5px 0 0 0;">Por favor no responda a este correo.</p>
        </div>

    </div>
</body>
</html>
