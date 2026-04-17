# Script de limpieza para testing del flujo 2FA

## Pasos para probar el login 2FA correctamente:

1. **Cerrar todas las sesiones activas:**
   - Abre tu navegador en modo incógnito/privado
   - O limpia las cookies del sitio

2. **Limpiar registros de log_accesos (OPCIONAL - solo para testing):**
   ```sql
   UPDATE log_accesos SET logout_at = NOW() WHERE logout_at IS NULL;
   ```

3. **Probar el flujo completo:**
   - Accede a `/` (login page)
   - Ingresa credenciales válidas
   - Deberías ser redirigido a `/2fa`
   - Revisa tu correo para el código de 6 dígitos
   - Ingresa el código
   - Deberías ser redirigido a `/dashboard`

## Si el problema persiste:

Verifica en la consola del navegador (F12) si hay errores de JavaScript o redirecciones inesperadas.

## Comando artisan para limpiar sesiones:

```bash
php artisan cache:clear
php artisan session:clear
php artisan config:clear
```
