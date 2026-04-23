<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secuencia Aprobada</title>
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
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 4px solid #229954;
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
        .success-box {
            background-color: #d5f4e6;
            border-left: 4px solid #27ae60;
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
            border-left: 2px solid #27ae60;
        }
        .status-badge {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
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
        .congratulations-box {
            background-color: #fff9e6;
            border: 2px solid #f39c12;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            text-align: center;
        }
        .congratulations-box p {
            color: #d68910;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
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
            <h1>✅ ¡Felicidades!</h1>
            <p>Secuencia Didáctica Aprobada</p>
        </div>

        <!-- Content -->
        <div class="content">
            
            <p class="greeting">
                Estimado(a) <strong>{{ $nombreDocente }}</strong>,
            </p>

            <p class="greeting">
                Nos complace informarte que tu secuencia didáctica ha sido <strong>aprobada exitosamente</strong> después de la revisión académica.
            </p>

            <!-- Caja de Felicidades -->
            <div class="congratulations-box">
                <p>🎉 ¡Excelente trabajo!</p>
                <p>Tu secuencia didáctica cumple con los estándares de calidad requeridos.</p>
            </div>

            <!-- Información General -->
            <div class="success-box">
                
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
                    <div class="info-label">👤 Revisor que Aprobó:</div>
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
                    <div class="info-label">💬 Comentarios del Revisor:</div>
                    <div class="motivo-box">{{ $motivo }}</div>
                </div>

            </div>

            <!-- Meta Information -->
            <div class="meta-info">
                <div class="meta-row">
                    ⏱️ <strong>Fecha de Aprobación:</strong> {{ now()->format('d/m/Y H:i') }}
                </div>
                <div class="meta-row">
                    📌 <strong>Período:</strong> {{ $secuencia->periodo?->nombre ?? 'Sin período' }}
                </div>
            </div>

            <p style="text-align: center; color: #27ae60; font-size: 14px; font-weight: bold; margin-top: 20px;">
                Tu secuencia didáctica ya está disponible en la plataforma.
            </p>

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
