<?php
// ARCHIVO TEMPORAL DE DIAGNÓSTICO — BORRAR DESPUÉS DE USAR
if (($_GET['k'] ?? '') !== 'pro2026diag') { http_response_code(404); exit; }
header('Content-Type: text/plain; charset=UTF-8');

echo "PHP version: " . phpversion() . "\n";
echo "mail() disponible: " . (function_exists('mail') ? 'SI' : 'NO') . "\n";
echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
echo "Ruta del script: " . __DIR__ . "\n";
echo "Puede escribir en ../logs/: " . (is_writable(__DIR__ . '/../logs') ? 'SI' : 'NO (crear la carpeta)') . "\n\n";

$ok = mail(
    'contacto@proadministra.cl',
    '=?UTF-8?B?' . base64_encode('Prueba de diagnóstico — acentos áéíóú ñ') . '?=',
    "Si lees esto, mail() funciona.\nAcentos de prueba: á é í ó ú ñ ¿? ¡!\nFecha: " . date('d-m-Y H:i:s'),
    implode("\r\n", [
        'From: Proadministra Web <contacto@proadministra.cl>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ]),
    '-f contacto@proadministra.cl'
);

echo "Resultado de mail(): " . ($ok ? 'TRUE (entregado al MTA)' : 'FALSE (fallo)') . "\n";
