<?php
declare(strict_types=1);

/* ─────────── Configuración ─────────── */
$DESTINO   = 'contacto@proadministra.cl';
$REMITENTE = 'contacto@proadministra.cl';   // debe ser un buzón real del dominio
$LOG       = __DIR__ . '/../logs/formularios.log';
$MAX_POR_HORA = 5;                           // por IP

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
// Sin cabeceras CORS: los formularios son del mismo origen y no las necesitan.

function responder(int $code, string $status, string $message): void {
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, 'error', 'Método no permitido.');
}

/* ─────────── Anti-inyección de cabeceras ─────────── */
function limpiar(string $v): string {
    return trim(preg_replace('/[\r\n\t]+/', ' ', $v));
}
function campo(string $k, string $def = ''): string {
    return isset($_POST[$k]) && $_POST[$k] !== '' ? limpiar((string) $_POST[$k]) : $def;
}

/* ─────────── Honeypot y trampa de tiempo ─────────── */
// Un bot rellena todo, incluido el campo oculto. Fingimos éxito para no avisarle.
if (campo('website') !== '') {
    responder(200, 'success', '¡Gracias por tu mensaje!');
}
$ts = (int) campo('ts', '0');
if ($ts > 0 && (time() - $ts) < 3) {   // enviado en menos de 3 segundos = bot
    responder(200, 'success', '¡Gracias por tu mensaje!');
}

/* ─────────── Límite por IP ─────────── */
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
$marcador = sys_get_temp_dir() . '/pa_' . md5($ip);
$envios = file_exists($marcador) ? (array) json_decode((string) file_get_contents($marcador), true) : [];
$envios = array_filter($envios, static fn($t) => $t > time() - 3600);
if (count($envios) >= $MAX_POR_HORA) {
    responder(429, 'error', 'Has enviado varios mensajes seguidos. Intenta más tarde o escríbenos a contacto@proadministra.cl');
}

/* ─────────── Datos ─────────── */
$origen    = campo('origen', 'Formulario Web');
$nombre    = campo('nombre');
$email     = campo('email');
$telefono  = campo('telefono', 'No especificado');
$servicio  = campo('servicio');
$comunidad = campo('comunidad');
$urgencia  = campo('urgencia');
// El cuerpo sí admite saltos de línea: no entra a las cabeceras.
$mensaje   = isset($_POST['mensaje']) ? trim((string) $_POST['mensaje']) : '';

/* ─────────── Validación ─────────── */
if ($nombre === '' || $email === '' || $mensaje === '') {
    responder(400, 'error', 'Por favor, completa todos los campos obligatorios.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(400, 'error', 'El correo electrónico no parece válido.');
}
if (mb_strlen($mensaje) > 5000) {
    responder(400, 'error', 'El mensaje es demasiado largo.');
}

/* ─────────── Cuerpo del correo ─────────── */
$L = str_repeat('─', 46);
$cuerpo  = "Nuevo mensaje desde proadministra.cl\n$L\n\n";
$cuerpo .= "Origen:     $origen\n";
$cuerpo .= "Nombre:     $nombre\n";
$cuerpo .= "Email:      $email\n";
$cuerpo .= "Teléfono:   $telefono\n";
if ($servicio  !== '') { $cuerpo .= "Servicio:   $servicio\n"; }
if ($comunidad !== '') { $cuerpo .= "Comunidad:  $comunidad\n"; }
if ($urgencia  !== '') { $cuerpo .= "Urgencia:   $urgencia\n"; }
$cuerpo .= "\nMensaje:\n$L\n$mensaje\n$L\n";
$cuerpo .= "Recibido:   " . date('d-m-Y H:i:s') . "\n";
$cuerpo .= "IP:         $ip\n";

/* ─────────── Cabeceras ─────────── */
$asunto = '=?UTF-8?B?' . base64_encode("Web Proadministra: $origen — $nombre") . '?=';

$headers = implode("\r\n", [
    "From: Proadministra Web <$REMITENTE>",
    "Reply-To: $email",
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: PHP/' . phpversion(),
]);

/* ─────────── Envío ─────────── */
$ok = mail($DESTINO, $asunto, $cuerpo, $headers, '-f ' . $REMITENTE);

/* ─────────── Registro ─────────── */
@file_put_contents($LOG, sprintf(
    "[%s] %-4s | %s | %s | %s | %s%s",
    date('c'), $ok ? 'OK' : 'FAIL', $origen, $nombre, $email, $ip, PHP_EOL
), FILE_APPEND | LOCK_EX);

if ($ok) {
    $envios[] = time();
    @file_put_contents($marcador, json_encode(array_values($envios)));
    responder(200, 'success', '¡Gracias por tu mensaje! Nos pondremos en contacto pronto.');
}

responder(500, 'error', 'No pudimos enviar tu mensaje. Escríbenos directamente a contacto@proadministra.cl');
