<?php
/**
 * Módulo para enviar emails usando PHPMailer
 */

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar autoload de Composer o los archivos de PHPMailer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    // Si no usas Composer, incluye los archivos manualmente
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
}

function enviarEmail($email, $asunto, $mensaje, $telefono = '') {
    try {
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'mensaje' => 'Email inválido: ' . $email
            ];
        }
        
        // Crear instancia de PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USER;
        $mail->Password = EMAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Desactivar verificación SSL (solo para desarrollo local)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Remitente
        $mail->setFrom(EMAIL_USER, EMAIL_FROM_NAME);
        $mail->addReplyTo(EMAIL_USER, EMAIL_FROM_NAME);
        
        // Destinatario
        $mail->addAddress($email);
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        
        // Crear HTML del email con diseño profesional
        $htmlBody = crearHTMLEmail($mensaje, $telefono);
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($mensaje);
        
        // Enviar
        $mail->send();
        
        return [
            'success' => true,
            'mensaje' => 'Email enviado correctamente',
            'destinatario' => $email
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Error al enviar email: ' . $mail->ErrorInfo,
            'error_detalle' => $e->getMessage()
        ];
    }
}

/**
 * Crear HTML profesional para el email
 */
function crearHTMLEmail($mensaje, $telefono = '') {
    // Convertir saltos de linea a <br>
    $mensajeHTML = nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'));
    
    // Boton de WhatsApp si hay telefono
    $botonWhatsApp = '';
    if (!empty($telefono)) {
        // Limpiar numero de telefono
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);
        $whatsappLink = "https://wa.me/" . $telefonoLimpio;
        $botonWhatsApp = '
        <div style="text-align: center; margin: 30px 0;">
            <a href="' . $whatsappLink . '" 
               style="display: inline-block; 
                      padding: 15px 30px; 
                      background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); 
                      color: white; 
                      text-decoration: none; 
                      border-radius: 50px; 
                      font-weight: bold;
                      box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);">
                Contactar por WhatsApp
            </a>
        </div>';
    }
    
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background: #f5f5f5;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%); padding: 30px; text-align: center;">
                                <h1 style="margin: 0; color: white; font-size: 28px;">
                                    Sistema de Gestion de Cartera
                                </h1>
                            </td>
                        </tr>
                        
                        <!-- Contenido -->
                        <tr>
                            <td style="padding: 40px 30px;">
                                <div style="color: #333; font-size: 16px; line-height: 1.8;">
                                    ' . $mensajeHTML . '
                                </div>
                                
                                ' . $botonWhatsApp . '
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background: #1a1a1a; padding: 20px; text-align: center;">
                                <p style="margin: 0; color: #888; font-size: 12px;">
                                    Este es un mensaje automatico del sistema de gestion de cartera
                                </p>
                                <p style="margin: 10px 0 0 0; color: #ff6b35; font-size: 12px;">
                                    &copy; ' . date('Y') . ' - Sistema de Envio Masivo
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    return $html;
}

/**
 * Verificar conexión con el servidor SMTP
 */
function verificarConexionSMTP() {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USER;
        $mail->Password = EMAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;
        $mail->Timeout = 10;
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Intentar conectar
        $mail->smtpConnect();
        
        return [
            'success' => true,
            'mensaje' => 'Conexión SMTP exitosa',
            'servidor' => EMAIL_HOST,
            'puerto' => EMAIL_PORT
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'mensaje' => 'Error de conexión SMTP: ' . $e->getMessage(),
            'servidor' => EMAIL_HOST,
            'puerto' => EMAIL_PORT
        ];
    }
}
?>