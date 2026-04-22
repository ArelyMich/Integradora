<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Comentario en Secuencia Didáctica</title>
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
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 4px solid #3498db;
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
            background-color: #ecf7ff;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-section {
            margin-bottom: 20px;
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
            border-left: 2px solid #3498db;
        }
        .comentario-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-wrap;
            color: #333;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
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
            <h1>📝 Nuevo Comentario</h1>
            <p>Secuencia Didáctica</p>
        </div>

        <!-- Content -->
        <div class="content">
            
            <p class="greeting">
                Estimado(a) <strong>{{ $nombreDocente }}</strong>,
            </p>

            <p class="greeting">
                Se ha registrado un nuevo comentario en tu secuencia didáctica. A continuación se detalla la información:
            </p>

            <!-- Información del Comentario -->
            <div class="info-box">
                
                <!-- Quien comenta -->
                <div class="info-section">
                    <div class="info-label">👤 Comentario realizado por:</div>
                    <div class="info-value">
                        <strong>
                            {{ trim($comentador->name ?? '') }} 
                            {{ trim($comentador->apellido_paterno ?? '') }} 
                            {{ trim($comentador->apellido_materno ?? '') }}
                        </strong>
                    </div>
                    <div class="info-value" style="border-left-color: #95a5a6; color: #7f8c8d; font-size: 13px;">
                        {{ $comentador->email ?? 'Sin correo registrado' }}
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #bdc3c7; margin: 15px 0;">

                <!-- Ubicación -->
                <div class="info-section">
                    <div class="info-label">📄 Ubicación:</div>
                    <div class="info-value">
                        Página <strong>{{ $comentario->page }}</strong>
                        @if($comentario->titulo)
                            | <strong>{{ $comentario->titulo }}</strong>
                        @endif
                    </div>
                </div>

                <hr style="border: none; border-top: 1px solid #bdc3c7; margin: 15px 0;">

                <!-- Contenido del Comentario -->
                <div class="info-section">
                    <div class="info-label">💬 Contenido del comentario:</div>
                    <div class="comentario-box">{{ $comentario->comentario }}</div>
                </div>

                @if($comentario->info)
                    <hr style="border: none; border-top: 1px solid #bdc3c7; margin: 15px 0;">
                    <div class="info-section">
                        <div class="info-label">ℹ️ Información adicional:</div>
                        <div class="info-value">{{ $comentario->info }}</div>
                    </div>
                @endif

            </div>

            <!-- Meta Information -->
            <div class="meta-info">
                <div class="meta-row">
                    ⏱️ <strong>Fecha del comentario:</strong> {{ $comentario->created_at->format('d/m/Y H:i') }}
                </div>
                <div class="meta-row">
                    📊 <strong>Estado:</strong> 
                    <span class="status-badge">{{ ucfirst($comentario->estatus) }}</span>
                </div>
            </div>

            <!-- Warning -->
            <div class="warning-box">
                <strong>⚠️ Nota importante:</strong> Por favor accede a la plataforma para ver el comentario en contexto y responder si es necesario.
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
