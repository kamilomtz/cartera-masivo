<?php
/**
 * Genera un archivo Excel de ejemplo para el sistema de envío masivo
 */

// Nombre del archivo
$filename = 'plantilla_cartera_' . date('Y-m-d') . '.csv';

// Headers para descarga
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Crear el archivo CSV
$output = fopen('php://output', 'w');

// Agregar BOM para UTF-8 (para que Excel reconozca los acentos)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados
fputcsv($output, ['nombre', 'telefono', 'email', 'valor_pagar']);

// Datos de ejemplo
$ejemplos = [
    ['Juan Pérez', '573001234567', 'juan.perez@email.com', '150000'],
    ['María López', '573009876543', 'maria.lopez@email.com', '250000'],
    ['Pedro García', '573005555555', 'pedro.garcia@email.com', '180000'],
    ['Ana Martínez', '573007777777', 'ana.martinez@email.com', '320000'],
    ['Carlos Rodríguez', '573008888888', 'carlos.rodriguez@email.com', '95000']
];

foreach ($ejemplos as $ejemplo) {
    fputcsv($output, $ejemplo);
}

fclose($output);
exit;
?>