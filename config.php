<?php
// ====================================
// CONFIGURACION DE EMAIL (Gmail/SMTP)
// ====================================

// Servidor SMTP
define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 587); // 587 para TLS, 465 para SSL

// Credenciales de Email
define('EMAIL_USER', 'celredcomunikt@gmail.com'); 
define('EMAIL_PASS', 'jntswbmnozfmyrnm'); // Contrasena de aplicacion de Gmail
define('EMAIL_FROM_NAME', 'Sistema de Gestion de Cartera');

// ====================================
// TELEFONO DE CONTACTO DE LA EMPRESA
// ====================================

// Este es el numero de WhatsApp que aparecer en el boton del email
// Los clientes haran clic aqui para contactarte
define('EMPRESA_WHATSAPP', '573203598880'); // CAMBIA ESTE NUMERO POR EL TUYO

// ====================================
// CONFIGURACION DE WHATSAPP (Node.js)
// ====================================

// Cambia a true despues de escanear el QR
define('WHATSAPP_HABILITADO', true);

// Servidor Node.js (whatsapp-server.js)
define('NODEJS_API_URL', 'http://localhost:3001');

// NOTA: Si cambias el puerto en whatsapp-server.js, 
// actualiza tambien NODEJS_API_URL aqui

// Para verificar que el servidor este corriendo:
// http://localhost:3000/qr

// ====================================
// CONFIGURACION DE SMS (Twilio)
// ====================================

// Habilitar/Deshabilitar SMS
define('SMS_HABILITADO', false); // Cambia a true despues de configurar Twilio

// Credenciales de Twilio (https://www.twilio.com/console)
define('TWILIO_ACCOUNT_SID', '');  // Tu Account SID
define('TWILIO_AUTH_TOKEN', '');   // Tu Auth Token
define('TWILIO_PHONE_FROM', '');   // Tu numero de Twilio (ej: +15551234567)

// ====================================
// CONFIGURACION GENERAL
// ====================================

define('LOG_FILE', __DIR__ . '/logs.json');
date_default_timezone_set('America/Bogota');

// Limites de envios por hora
define('MAX_EMAILS_PER_HOUR', 500);
define('MAX_WHATSAPP_PER_HOUR', 1000);

// ====================================
// INSTRUCCIONES RAPIDAS
// ====================================

/*
 PASO 1: CONFIGURAR GMAIL

1. Ve a: https://myaccount.google.com/security
2. Activa "Verificacion en 2 pasos"
3. Ve a "Contrasenas de aplicaciones"
4. Selecciona "Correo" y "Otro dispositivo"
5. Genera una nueva contrasena (16 caracteres)
6. Reemplaza EMAIL_PASS arriba con esa contrasena

 PASO 2: INSTALAR SERVIDOR NODE.JS

1. Instala Node.js: https://nodejs.org (version LTS)

2. Verifica instalacion:
   node --version
   npm --version

3. Instala dependencias:
   npm install

4. Ejecuta el servidor:
   node whatsapp-server.js

5. Abre http://localhost:3000/qr y escanea el QR con WhatsApp

6. Verifica en http://localhost:3000/status que diga "ready": true

 PASO 3: INSTALAR PHPMAILER

OPCION A: Con Composer (recomendado)
1. Instala Composer: https://getcomposer.org/download/
2. En la carpeta del proyecto ejecuta:
   composer require phpmailer/phpmailer

OPCION B: Manual (sin Composer)
1. Descarga: https://github.com/PHPMailer/PHPMailer/archive/master.zip
2. Extrae la carpeta "PHPMailer-master" en tu proyecto
3. Renombrala a "PHPMailer"
4. La estructura debe ser:
   - PHPMailer/
     - src/
       - Exception.php
       - PHPMailer.php
       - SMTP.php

 VERIFICAR INSTALACION

1. Gmail configurado: EMAIL_PASS con contrasena de aplicacion
2. Node.js corriendo: http://localhost:3000/status debe responder
3. WhatsApp conectado: "ready": true en /status
4. PHPMailer instalado: carpeta vendor/autoload.php o PHPMailer/src/

 EJECUTAR DIAGNOSTICO

http://localhost/tu-proyecto/test_sistema.php

 FORMATO DEL EXCEL:

Columnas requeridas:
- nombre: Nombre del contacto
- telefono: Numero con codigo de pais (ej: 573001234567)
- email: Correo electronico
- valor_pagar: Monto numerico (ej: 150000)

*/
?>