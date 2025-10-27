@echo off
chcp 65001 >nul
echo ========================================
echo 🚀 INSTALADOR WAHA - SIN AUTENTICACIÓN
echo ========================================
echo.

echo [1/4] Deteniendo WAHA anterior...
docker stop waha 2>nul
docker rm waha 2>nul
echo ✅ Listo
echo.

echo [2/4] Creando nuevo contenedor WAHA...
echo Este proceso puede tardar 1-2 minutos...
echo.

docker run -d -p 3000:3000 --name waha --restart=always ^
  -e WAHA_DASHBOARD_ENABLED=true ^
  -e WAHA_API_KEY= ^
  -e WAHA_DASHBOARD_USERNAME= ^
  -e WAHA_DASHBOARD_PASSWORD= ^
  -e WHATSAPP_SWAGGER_USERNAME= ^
  -e WHATSAPP_SWAGGER_PASSWORD= ^
  devlikeapro/waha

if %ERRORLEVEL% EQU 0 (
    echo ✅ Contenedor creado exitosamente
) else (
    echo ❌ Error al crear contenedor
    echo.
    echo Verifica que Docker Desktop esté corriendo
    pause
    exit /b 1
)
echo.

echo [3/4] Esperando que WAHA inicie...
timeout /t 30 /nobreak >nul
echo ✅ Listo
echo.

echo [4/4] Verificando estado...
docker ps | findstr waha >nul
if %ERRORLEVEL% EQU 0 (
    echo ✅ WAHA está corriendo correctamente
) else (
    echo ⚠️ WAHA no está corriendo
    docker logs waha
)
echo.

echo ========================================
echo ✅ INSTALACIÓN COMPLETADA
echo ========================================
echo.
echo 📱 PRÓXIMOS PASOS:
echo.
echo 1. Abre tu navegador en: http://localhost:3000
echo 2. Ya NO debería pedir contraseña
echo 3. Ve a "Sessions" y crea la sesión "cartera"
echo 4. Escanea el QR con WhatsApp
echo.
echo 💡 IMPORTANTE:
echo En config.php, asegúrate de tener:
echo   define('WHATSAPP_HABILITADO', true);
echo   define('WAHA_API_KEY', '');
echo.
echo ========================================
pause