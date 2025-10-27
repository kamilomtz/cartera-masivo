<?php
/**
 * Función para enviar mensajes por WhatsApp usando Node.js server
 */
function enviarWhatsApp($telefono, $mensaje) {
    // Verificar si WhatsApp está habilitado
    if (!defined('WHATSAPP_HABILITADO') || WHATSAPP_HABILITADO === false) {
        return [
            'success' => true,
            'mensaje' => '✅ WhatsApp simulado (modo desarrollo)',
            'telefono' => $telefono,
            'nota' => 'Para envío real, configura el servidor Node.js'
        ];
    }
    
    // Limpiar número de teléfono
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    
    // Verificar que el número tenga formato correcto
    if (strlen($telefono) < 10) {
        return [
            'success' => false,
            'mensaje' => 'Número de teléfono inválido: ' . $telefono
        ];
    }
    
    // Si el número no empieza con código de país, agregar 57 (Colombia)
    if (strlen($telefono) === 10) {
        $telefono = '57' . $telefono;
    }
    
    // Intentar con servidor Node.js
    if (defined('NODEJS_API_URL')) {
        return enviarConNodeJS($telefono, $mensaje);
    }
    
    // Intentar con WAHA
    if (defined('WAHA_API_URL') && defined('WAHA_SESSION')) {
        return enviarConWAHA($telefono, $mensaje);
    }
    
    return [
        'success' => false,
        'mensaje' => 'No hay servicio de WhatsApp configurado'
    ];
}

/**
 * Enviar mensaje usando servidor Node.js personalizado
 */
function enviarConNodeJS($telefono, $mensaje) {
    try {
        $url = NODEJS_API_URL . '/send';
        
        $data = [
            'telefono' => $telefono,
            'mensaje' => $mensaje
        ];
        
        $jsonData = json_encode($data);
        
        // Configurar cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Manejar errores de conexión
        if ($error) {
            return [
                'success' => false,
                'mensaje' => 'Error de conexión con servidor Node.js: ' . $error,
                'solucion' => 'Asegúrate de que el servidor esté corriendo: node whatsapp-server.js'
            ];
        }
        
        // Servidor no disponible
        if ($httpCode === 503) {
            return [
                'success' => false,
                'mensaje' => 'WhatsApp no está conectado. Escanea el QR primero.',
                'http_code' => 503,
                'solucion' => 'Ve a http://localhost:3000/qr para obtener el QR'
            ];
        }
        
        // Número no registrado
        if ($httpCode === 404) {
            return [
                'success' => false,
                'mensaje' => 'El número no está registrado en WhatsApp',
                'telefono' => $telefono
            ];
        }
        
        // Éxito
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            return [
                'success' => true,
                'mensaje' => 'Mensaje enviado por WhatsApp (Node.js)',
                'telefono' => $telefono,
                'response' => $responseData
            ];
        }
        
        // Error específico
        $responseData = json_decode($response, true);
        $errorMsg = isset($responseData['mensaje']) ? $responseData['mensaje'] : $response;
        
        return [
            'success' => false,
            'mensaje' => 'Error Node.js (HTTP ' . $httpCode . '): ' . $errorMsg,
            'http_code' => $httpCode
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Excepción en Node.js: ' . $e->getMessage()
        ];
    }
}

/**
 * Enviar mensaje usando WAHA (WhatsApp HTTP API)
 */
function enviarConWAHA($telefono, $mensaje) {
    try {
        $url = WAHA_API_URL . '/api/sendText';
        
        $chatId = $telefono . '@c.us';
        
        $data = [
            'session' => WAHA_SESSION,
            'chatId' => $chatId,
            'text' => $mensaje
        ];
        
        $jsonData = json_encode($data);
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if (defined('WAHA_API_KEY') && !empty(WAHA_API_KEY)) {
            $headers[] = 'X-Api-Key: ' . WAHA_API_KEY;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'mensaje' => 'Error de conexión con WAHA: ' . $error
            ];
        }
        
        if ($httpCode === 200 || $httpCode === 201) {
            return [
                'success' => true,
                'mensaje' => 'Mensaje enviado por WhatsApp (WAHA)',
                'telefono' => $telefono,
                'response' => json_decode($response, true)
            ];
        }
        
        $responseData = json_decode($response, true);
        $errorMsg = isset($responseData['message']) ? $responseData['message'] : $response;
        
        return [
            'success' => false,
            'mensaje' => 'Error WAHA (HTTP ' . $httpCode . '): ' . $errorMsg,
            'http_code' => $httpCode
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Excepción en WAHA: ' . $e->getMessage()
        ];
    }
}

/**
 * Verificar estado de conexión de WhatsApp
 */
function verificarEstadoWhatsApp() {
    if (!defined('WHATSAPP_HABILITADO') || WHATSAPP_HABILITADO === false) {
        return [
            'conectado' => false,
            'mensaje' => 'WhatsApp no habilitado'
        ];
    }
    
    // Verificar Node.js server
    if (defined('NODEJS_API_URL')) {
        try {
            $url = NODEJS_API_URL . '/status';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return [
                    'conectado' => $data['ready'] ?? false,
                    'mensaje' => 'Estado Node.js: ' . ($data['ready'] ? 'Conectado' : 'Desconectado'),
                    'servicio' => 'Node.js',
                    'qr_disponible' => isset($data['qr']) && !empty($data['qr']),
                    'detalles' => $data
                ];
            }
        } catch (Exception $e) {
            return [
                'conectado' => false,
                'mensaje' => 'Error al verificar Node.js: ' . $e->getMessage(),
                'servicio' => 'Node.js'
            ];
        }
    }
    
    // Verificar WAHA
    if (defined('WAHA_API_URL') && defined('WAHA_SESSION')) {
        try {
            $url = WAHA_API_URL . '/api/sessions/' . WAHA_SESSION;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $estado = $data['status'] ?? 'unknown';
                
                return [
                    'conectado' => ($estado === 'WORKING'),
                    'mensaje' => 'Estado WAHA: ' . $estado,
                    'servicio' => 'WAHA',
                    'detalles' => $data
                ];
            }
        } catch (Exception $e) {
            return [
                'conectado' => false,
                'mensaje' => 'Error al verificar WAHA: ' . $e->getMessage(),
                'servicio' => 'WAHA'
            ];
        }
    }
    
    return [
        'conectado' => false,
        'mensaje' => 'Ningún servicio de WhatsApp configurado'
    ];
}
?>