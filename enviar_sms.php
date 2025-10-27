<?php
/**
 * Módulo para enviar SMS
 * Soporta: Twilio (recomendado), AWS SNS, y modo simulación
 */

function enviarSMS($telefono, $mensaje) {
    // Verificar si SMS está habilitado
    if (!defined('SMS_HABILITADO') || SMS_HABILITADO === false) {
        return [
            'success' => true,
            'mensaje' => '✅ SMS simulado (modo desarrollo)',
            'telefono' => $telefono,
            'nota' => 'Para envío real, configura Twilio en config.php'
        ];
    }
    
    // Limpiar número de teléfono
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    
    // Verificar formato
    if (strlen($telefono) < 10) {
        return [
            'success' => false,
            'mensaje' => 'Número de teléfono inválido: ' . $telefono
        ];
    }
    
    // Agregar código de país si no lo tiene
    if (strlen($telefono) === 10) {
        $telefono = '57' . $telefono; // Colombia
    }
    
    // Intentar con Twilio
    if (defined('TWILIO_ACCOUNT_SID') && defined('TWILIO_AUTH_TOKEN')) {
        return enviarConTwilio($telefono, $mensaje);
    }
    
    // Intentar con AWS SNS
    if (defined('AWS_SNS_KEY') && defined('AWS_SNS_SECRET')) {
        return enviarConAWS($telefono, $mensaje);
    }
    
    return [
        'success' => false,
        'mensaje' => 'No hay servicio de SMS configurado'
    ];
}

/**
 * Enviar SMS con Twilio
 * Servicio recomendado y más usado
 */
function enviarConTwilio($telefono, $mensaje) {
    try {
        // Validar configuración
        if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN') || !defined('TWILIO_PHONE_FROM')) {
            return [
                'success' => false,
                'mensaje' => 'Credenciales de Twilio no configuradas'
            ];
        }
        
        $accountSid = TWILIO_ACCOUNT_SID;
        $authToken = TWILIO_AUTH_TOKEN;
        $from = TWILIO_PHONE_FROM;
        
        // Limitar mensaje a 160 caracteres (estándar SMS)
        if (strlen($mensaje) > 160) {
            $mensaje = substr($mensaje, 0, 157) . '...';
        }
        
        // URL de la API de Twilio
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
        
        // Datos del mensaje
        $data = [
            'From' => $from,
            'To' => '+' . $telefono,
            'Body' => $mensaje
        ];
        
        // Configurar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, $accountSid . ':' . $authToken);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Manejar errores de conexión
        if ($error) {
            return [
                'success' => false,
                'mensaje' => 'Error de conexión con Twilio: ' . $error
            ];
        }
        
        // Parsear respuesta
        $result = json_decode($response, true);
        
        // Verificar éxito
        if ($httpCode === 200 || $httpCode === 201) {
            return [
                'success' => true,
                'mensaje' => 'SMS enviado correctamente',
                'telefono' => $telefono,
                'sid' => $result['sid'] ?? null,
                'costo' => $result['price'] ?? 'N/A',
                'estado' => $result['status'] ?? 'sent'
            ];
        }
        
        // Error específico de Twilio
        $errorMsg = $result['message'] ?? 'Error desconocido';
        $errorCode = $result['code'] ?? $httpCode;
        
        return [
            'success' => false,
            'mensaje' => "Error Twilio ({$errorCode}): {$errorMsg}",
            'http_code' => $httpCode,
            'response' => $result
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Excepción en Twilio: ' . $e->getMessage()
        ];
    }
}

/**
 * Enviar SMS con AWS SNS
 * Alternativa a Twilio
 */
function enviarConAWS($telefono, $mensaje) {
    // Implementación básica de AWS SNS
    // Requiere AWS SDK o llamadas directas a la API
    
    return [
        'success' => false,
        'mensaje' => 'AWS SNS no implementado aún. Usa Twilio.'
    ];
}

/**
 * Obtener costo estimado por SMS
 */
function obtenerCostoSMS($pais = 'CO') {
    // Costos aproximados de Twilio (USD por SMS)
    $costos = [
        'CO' => 0.0183, // Colombia
        'US' => 0.0079, // Estados Unidos
        'MX' => 0.0140, // México
        'AR' => 0.0350, // Argentina
        'CL' => 0.0280, // Chile
        'PE' => 0.0320, // Perú
    ];
    
    return [
        'pais' => $pais,
        'costo_usd' => $costos[$pais] ?? 0.02,
        'moneda' => 'USD',
        'nota' => 'Costo aproximado por mensaje'
    ];
}

/**
 * Validar balance de Twilio
 */
function verificarBalanceTwilio() {
    if (!defined('TWILIO_ACCOUNT_SID') || !defined('TWILIO_AUTH_TOKEN')) {
        return [
            'success' => false,
            'mensaje' => 'Twilio no configurado'
        ];
    }
    
    try {
        $accountSid = TWILIO_ACCOUNT_SID;
        $authToken = TWILIO_AUTH_TOKEN;
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Balance.json";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $accountSid . ':' . $authToken);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            
            return [
                'success' => true,
                'balance' => $data['balance'] ?? '0',
                'moneda' => $data['currency'] ?? 'USD',
                'mensaje' => 'Balance disponible: ' . ($data['balance'] ?? '0') . ' ' . ($data['currency'] ?? 'USD')
            ];
        }
        
        return [
            'success' => false,
            'mensaje' => 'No se pudo obtener balance (HTTP ' . $httpCode . ')'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Error al verificar balance: ' . $e->getMessage()
        ];
    }
}
?>