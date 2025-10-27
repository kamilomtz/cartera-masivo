<?php
require_once 'config.php';

header('Content-Type: application/json');

$logs = [];
if (file_exists(LOG_FILE)) {
    $logs = json_decode(file_get_contents(LOG_FILE), true) ?? [];
}

$stats = [
    'total_enviados' => count($logs),
    'hoy' => 0,
    'esta_semana' => 0,
    'este_mes' => 0,
    'por_canal' => [
        'whatsapp' => 0,
        'email' => 0
    ]
];

$hoy = date('Y-m-d');
$inicioSemana = date('Y-m-d', strtotime('monday this week'));
$inicioMes = date('Y-m-01');

foreach ($logs as $log) {
    $fecha = substr($log['fecha'], 0, 10);
    
    if ($fecha === $hoy) $stats['hoy']++;
    if ($fecha >= $inicioSemana) $stats['esta_semana']++;
    if ($fecha >= $inicioMes) $stats['este_mes']++;
    
    if (isset($log['canales'])) {
        foreach ($log['canales'] as $canal) {
            if (isset($stats['por_canal'][$canal])) {
                $stats['por_canal'][$canal]++;
            }
        }
    }
}

echo json_encode($stats);
?>