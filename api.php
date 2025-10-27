<?php
// Limpiar cualquier output previo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function sendJson($data) {
    if (ob_get_length()) ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar configuraciÃ³n
if (!file_exists(__DIR__ . '/config.php')) {
    sendJson(['success' => false, 'mensaje' => 'config.php no encontrado']);
}
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    enviarNotificacion();
} else {
    sendJson(['success' => false, 'mensaje' => 'MÃ©todo no permitido']);
}

function enviarNotificacion() {
    $canales = $_POST['canales'] ?? [];
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? 'NotificaciÃ³n');
    $mensaje = trim($_POST['mensaje'] ?? '');
    
    if (empty($canales)) {
        sendJson(['success' => false, 'mensaje' => 'No se especificaron canales']);
    }
    
    if (empty($mensaje)) {
        sendJson(['success' => false, 'mensaje' => 'El mensaje es requerido']);
    }
    
    $resultados = [];
    $exitos = 0;
    $errores = [];
    
    // Enviar por Email
    if (in_array('email', $canales) && !empty($email)) {
        if (!file_exists(__DIR__ . '/enviar_email.php')) {
            $errores[] = 'Email: enviar_email.php no encontrado';
        } else {
            require_once __DIR__ . '/enviar_email.php';
            
            // Pasar el telÃ©fono para el botÃ³n de WhatsApp
            $telefonoParaEmail = !empty($telefono) ? $telefono : '';
            
            $resultEmail = enviarEmail($email, $asunto, $mensaje, $telefonoParaEmail);
            $resultados['email'] = $resultEmail;
            
            if (isset($resultEmail['success']) && $resultEmail['success']) {
                $exitos++;
            } else {
                $errores[] = 'Email: ' . ($resultEmail['mensaje'] ?? 'Error desconocido');
            }
        }
    }
    
    // Enviar por WhatsApp
    if (in_array('whatsapp', $canales) && !empty($telefono)) {
        if (!file_exists(__DIR__ . '/enviar_whatsapp.php')) {
            $errores[] = 'WhatsApp: enviar_whatsapp.php no encontrado';
        } else {
            require_once __DIR__ . '/enviar_whatsapp.php';
            $resultWhatsApp = enviarWhatsApp($telefono, $mensaje);
            $resultados['whatsapp'] = $resultWhatsApp;
            
            if (isset($resultWhatsApp['success']) && $resultWhatsApp['success']) {
                $exitos++;
            } else {
                $errores[] = 'WhatsApp: ' . ($resultWhatsApp['mensaje'] ?? 'Error desconocido');
            }
        }
    }
    
    // Enviar SMS
    if (in_array('sms', $canales) && !empty($telefono)) {
        if (!file_exists(__DIR__ . '/enviar_sms.php')) {
            $errores[] = 'SMS: enviar_sms.php no encontrado';
        } else {
            require_once __DIR__ . '/enviar_sms.php';
            $resultSMS = enviarSMS($telefono, $mensaje);
            $resultados['sms'] = $resultSMS;
            
            if (isset($resultSMS['success']) && $resultSMS['success']) {
                $exitos++;
            } else {
                $errores[] = 'SMS: ' . ($resultSMS['mensaje'] ?? 'Error desconocido');
            }
        }
    }
    
    // Guardar log
    guardarLog([
        'canales' => $canales,
        'telefono' => $telefono,
        'email' => $email,
        'asunto' => $asunto,
        'mensaje_preview' => mb_substr($mensaje, 0, 100),
        'exitos' => $exitos,
        'fecha' => date('Y-m-d H:i:s')
    ]);
    
    if ($exitos > 0) {
        sendJson([
            'success' => true,
            'mensaje' => "Enviado por {$exitos} canal(es)",
            'exitos' => $exitos,
            'resultados' => $resultados
        ]);
    } else {
        sendJson([
            'success' => false,
            'mensaje' => 'No se pudo enviar por ningÃºn canal: ' . implode(', ', $errores),
            'errores' => $errores,
            'resultados' => $resultados
        ]);
    }
}

function guardarLog($data) {
    try {
        $logFile = defined('LOG_FILE') ? LOG_FILE : __DIR__ . '/logs.json';
        $logs = [];
        
        if (file_exists($logFile)) {
            $content = @file_get_contents($logFile);
            if ($content) {
                $logs = json_decode($content, true);
                if (!is_array($logs)) $logs = [];
            }
        }
        
        $logs[] = $data;
        
        if (count($logs) > 500) {
            $logs = array_slice($logs, -500);
        }
        
        $jsonData = json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        @file_put_contents($logFile, $jsonData);
    } catch (Exception $e) {
        // Silenciar errores de log
    }
}
?>