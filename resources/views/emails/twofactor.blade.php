<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación 2FA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #004A80 0%, #003761 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .content {
            padding: 30px 20px;
            text-align: center;
        }
        .content p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin: 15px 0;
        }
        .code-box {
            background-color: #f8f9fa;
            border: 2px solid #004A80;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .code {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #004A80;
            font-family: 'Courier New', monospace;
            margin: 0;
        }
        .expiry {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            color: #777;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🔐 Verificación en Dos Pasos</h1>
        </div>

        <!-- Contenido Principal -->
        <div class="content">
            <p>¡Hola,</p>
            
            <p>Hemos recibido una solicitud para acceder a tu cuenta. Por seguridad, necesitamos que verifiques tu identidad.</p>
            
            <p><strong>Tu código de verificación es:</strong></p>
            
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>

            <p>Ingresa este código en la pantalla de verificación para completar tu inicio de sesión.</p>

            <!-- Advertencia de Expiración -->
            <div class="expiry">
                ⏱️ <strong>Este código expira en 5 minutos.</strong> Si no lo usas en este tiempo, deberás solicitar uno nuevo.
            </div>

            <!-- Información Adicional -->
            <p style="color: #999; font-size: 14px; margin-top: 30px;">
                <strong>Si no solicitaste este código:</strong><br>
                Por favor, ignora este correo y considera cambiar tu contraseña para mayor seguridad.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>AJASOFTWARE - Sistema de Gestión de Secuencias Didácticas</strong></p>
            <p>Este es un correo automático, por favor no responder.</p>
            <p>&copy; 2026 UTH. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
