<?php
/**
 * Script de diagnóstico para verificar que todo esté funcionando
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico del Sistema</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #2a2a2a;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 2px solid #ff6b35;
        }
        h1 { color: #ff6b35; margin-bottom: 30px; text-align: center; }
        .test-section {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ff6b35;
        }
        .test-section h2 {
            color: #ff6b35;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .test-item.success { background: #1a3d1a; border-left: 4px solid #4ade80; }
        .test-item.error { background: #3d1a1a; border-left: 4px solid #f87171; }
        .test-item.warning { background: #3d3d1a; border-left: 4px solid #fbbf24; }
        .test-label { color: #ccc; font-weight: 600; }
        .test-status { font-weight: bold; font-size: 18px; }
        .test-status.success { color: #4ade80; }
        .test-status.error { color: #f87171; }
        .test-status.warning { color: #fbbf24; }
        .test-details {
            color: #888;
            font-size: 13px;
            margin-top: 8px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.5);
        }
        .code {
            background: #000;
            padding: 15px;
            border-radius: 8px;
            color: #4ade80;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        pre { margin: 0; }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico del Sistema</h1>

        <!-- PHP & EXTENSIONES -->
        <div class="test-section">
            <h2>⚙️ Configuración PHP</h2>
            <?php
            $phpVersion = phpversion();
            $phpOK = version_compare($phpVersion, '7.4.0', '>=');
            ?>
            <div class="test-item <?php echo $phpOK ? 'success' : 'error'; ?>">
                <div>
                    <div class="test-label">Versión de PHP</div>
                    <div class="test-details"><?php echo $phpVersion; ?></div>
                </div>
                <div class="test-status <?php echo $phpOK ? 'success' : 'error'; ?>">
                    <?php echo $phpOK ? '✅' : '❌'; ?>
                </div>
            </div>

            <?php
            $extensions = ['curl', 'openssl', 'mbstring'];
            foreach ($extensions as $ext) {
                $loaded = extension_loaded($ext);
                ?>
                <div class="test-item <?php echo $loaded ? 'success' : 'error'; ?>">
                    <div>
                        <div class="test-label">Extensión: <?php echo $ext; ?></div>
                    </div>
                    <div class="test-status <?php echo $loaded ? 'success' : 'error'; ?>">
                        <?php echo $loaded ? '✅' : '❌'; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- PHPMAILER -->
        <div class="test-section">
            <h2>📧 PHPMailer</h2>
            <?php
            $phpmailerInstalled = false;
            $phpmailerPath = '';
            
            if (file_exists(__DIR__ . '/vendor/autoload.php')) {
                $phpmailerInstalled = true;
                $phpmailerPath = 'vendor/autoload.php (Composer)';
            } elseif (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
                $phpmailerInstalled = true;
                $phpmailerPath = 'PHPMailer/src/ (Manual)';
            }
            ?>
            <div class="test-item <?php echo $phpmailerInstalled ? 'success' : 'error'; ?>">
                <div>
                    <div class="test-label">PHPMailer instalado</div>
                    <div class="test-details"><?php echo $phpmailerPath ?: 'No encontrado'; ?></div>
                </div>
                <div class="test-status <?php echo $phpmailerInstalled ? 'success' : 'error'; ?>">
                    <?php echo $phpmailerInstalled ? '✅' : '❌'; ?>
                </div>
            </div>

            <?php if (!$phpmailerInstalled): ?>
                <div class="code">
                    <pre># Instalar con Composer:
composer require phpmailer/phpmailer

# O descargar manualmente:
# https://github.com/PHPMailer/PHPMailer/archive/master.zip</pre>
                </div>
            <?php endif; ?>

            <?php if ($phpmailerInstalled): ?>
                <div class="test-item <?php echo !empty(EMAIL_PASS) ? 'success' : 'error'; ?>">
                    <div>
                        <div class="test-label">Configuración Email</div>
                        <div class="test-details">
                            Host: <?php echo EMAIL_HOST; ?><br>
                            User: <?php echo EMAIL_USER; ?><br>
                            Pass: <?php echo !empty(EMAIL_PASS) ? '****' . substr(EMAIL_PASS, -4) : 'NO CONFIGURADO'; ?>
                        </div>
                    </div>
                    <div class="test-status <?php echo !empty(EMAIL_PASS) ? 'success' : 'error'; ?>">
                        <?php echo !empty(EMAIL_PASS) ? '✅' : '❌'; ?>
                    </div>
                </div>

                <?php
                // Test conexión SMTP
                if ($phpmailerInstalled && !empty(EMAIL_PASS)) {
                    require_once 'enviar_email.php';
                    $smtpTest = verificarConexionSMTP();
                    ?>
                    <div class="test-item <?php echo $smtpTest['success'] ? 'success' : 'error'; ?>">
                        <div>
                            <div class="test-label">Conexión SMTP</div>
                            <div class="test-details"><?php echo $smtpTest['mensaje']; ?></div>
                        </div>
                        <div class="test-status <?php echo $smtpTest['success'] ? 'success' : 'error'; ?>">
                            <?php echo $smtpTest['success'] ? '✅' : '❌'; ?>
                        </div>
                    </div>
                <?php } ?>
            <?php endif; ?>
        </div>

        <!-- WHATSAPP / NODE.JS -->
        <div class="test-section">
            <h2>📱 WhatsApp (Node.js)</h2>
            <?php
            $nodeJSRunning = false;
            $nodeJSReady = false;
            $nodeJSStatus = [];
            
            if (defined('NODEJS_API_URL')) {
                $ch = curl_init(NODEJS_API_URL . '/status');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                
                $response = @curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200 && $response) {
                    $nodeJSStatus = json_decode($response, true);
                    $nodeJSRunning = true;
                    $nodeJSReady = $nodeJSStatus['ready'] ?? false;
                }
            }
            ?>
            
            <div class="test-item <?php echo WHATSAPP_HABILITADO ? 'success' : 'warning'; ?>">
                <div>
                    <div class="test-label">WhatsApp habilitado en config.php</div>
                </div>
                <div class="test-status <?php echo WHATSAPP_HABILITADO ? 'success' : 'warning'; ?>">
                    <?php echo WHATSAPP_HABILITADO ? '✅' : '⚠️'; ?>
                </div>
            </div>

            <div class="test-item <?php echo $nodeJSRunning ? 'success' : 'error'; ?>">
                <div>
                    <div class="test-label">Servidor Node.js corriendo</div>
                    <div class="test-details"><?php echo NODEJS_API_URL; ?></div>
                </div>
                <div class="test-status <?php echo $nodeJSRunning ? 'success' : 'error'; ?>">
                    <?php echo $nodeJSRunning ? '✅' : '❌'; ?>
                </div>
            </div>

            <?php if ($nodeJSRunning): ?>
                <div class="test-item <?php echo $nodeJSReady ? 'success' : 'warning'; ?>">
                    <div>
                        <div class="test-label">WhatsApp conectado</div>
                        <div class="test-details">
                            <?php 
                            if ($nodeJSReady) {
                                echo 'Listo para enviar mensajes';
                            } else {
                                echo 'Necesita escanear código QR';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="test-status <?php echo $nodeJSReady ? 'success' : 'warning'; ?>">
                        <?php echo $nodeJSReady ? '✅' : '⚠️'; ?>
                    </div>
                </div>

                <?php if (!$nodeJSReady): ?>
                    <div class="code">
                        <pre>Para conectar WhatsApp:
1. Abre: <a href="<?php echo NODEJS_API_URL; ?>/qr" target="_blank" style="color: #ff6b35;"><?php echo NODEJS_API_URL; ?>/qr</a>
2. Escanea el código QR con WhatsApp
3. Espera la confirmación "WhatsApp conectado"</pre>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="code">
                    <pre># Instalar Node.js:
# https://nodejs.org (versión LTS)

# Instalar dependencias:
npm install whatsapp-web.js qrcode-terminal express body-parser

# Ejecutar servidor:
node whatsapp-server.js

# Verificar:
# http://localhost:3000/status</pre>
                </div>
            <?php endif; ?>
        </div>

        <!-- ARCHIVOS -->
        <div class="test-section">
            <h2>📁 Archivos del Sistema</h2>
            <?php
            $files = [
                'config.php' => 'Configuración',
                'api.php' => 'API principal',
                'enviar_email.php' => 'Módulo Email',
                'enviar_whatsapp.php' => 'Módulo WhatsApp',
                'sistema.html' => 'Interfaz del sistema',
                'login.html' => 'Página de login'
            ];

            foreach ($files as $file => $desc) {
                $exists = file_exists(__DIR__ . '/' . $file);
                ?>
                <div class="test-item <?php echo $exists ? 'success' : 'error'; ?>">
                    <div>
                        <div class="test-label"><?php echo $desc; ?></div>
                        <div class="test-details"><?php echo $file; ?></div>
                    </div>
                    <div class="test-status <?php echo $exists ? 'success' : 'error'; ?>">
                        <?php echo $exists ? '✅' : '❌'; ?>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- RESUMEN -->
        <div class="test-section">
            <h2>📊 Resumen</h2>
            <?php
            $allOK = $phpOK && $phpmailerInstalled && !empty(EMAIL_PASS) && $nodeJSRunning && $nodeJSReady;
            ?>
            <div class="test-item <?php echo $allOK ? 'success' : 'warning'; ?>">
                <div>
                    <div class="test-label" style="font-size: 18px;">
                        <?php if ($allOK): ?>
                            🎉 ¡Todo está configurado correctamente!
                        <?php else: ?>
                            ⚠️ Hay algunas configuraciones pendientes
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCIONES -->
        <div class="actions">
            <?php if ($nodeJSRunning && !$nodeJSReady): ?>
                <a href="<?php echo NODEJS_API_URL; ?>/qr" target="_blank" class="btn">
                    📱 Conectar WhatsApp
                </a>
            <?php endif; ?>
            
            <a href="sistema.html" class="btn">
                🚀 Ir al Sistema
            </a>
            
            <button onclick="location.reload()" class="btn" style="background: #333;">
                🔄 Volver a Verificar
            </button>
        </div>
    </div>
</body>
</html>