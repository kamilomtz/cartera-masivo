<?php
$data = [
    'telefono' => '573152506315', // TU NÚMERO
    'mensaje' => 'Prueba del sistema con Baileys'
];

$ch = curl_init('http://localhost:3001/send');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

header('Content-Type: application/json');
echo $response;
?>
```

Ejecuta:
```
http://localhost/notificacion/test-baileys.php