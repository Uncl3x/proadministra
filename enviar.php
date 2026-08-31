<?xml version="1.0" encoding="UTF-8"?>
<?php
// Permitir solicitudes desde cualquier origen (CORS) - Útil si se prueba desde otro dominio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}

// Recibir el JSON enviado por Javascript
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Extraer variables
$origen = isset($data['origen']) ? $data['origen'] : 'Formulario Web';
$nombre = isset($data['nombre']) ? trim($data['nombre']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$telefono = isset($data['telefono']) ? trim($data['telefono']) : 'No especificado';
$mensaje = isset($data['mensaje']) ? trim($data['mensaje']) : '';
$servicio = isset($data['servicio']) ? trim($data['servicio']) : 'No especificado';
$direccion = isset($data['direccion']) ? trim($data['direccion']) : 'No especificada';

// Validar campos obligatorios
if (empty($nombre) || empty($email) || empty($mensaje)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Por favor, completa todos los campos obligatorios."]);
    exit;
}

// Configuración del correo
$to = "contacto@proadministra.cl"; // CAMBIA ESTO A TU CORREO REAL
$subject = "Nuevo Mensaje de Contacto: " . $origen;

// Cuerpo del correo
$email_content = "Has recibido un nuevo mensaje desde tu sitio web.\n\n";
$email_content .= "Origen: $origen\n";
$email_content .= "Nombre: $nombre\n";
$email_content .= "Email: $email\n";
$email_content .= "Teléfono: $telefono\n";

// Si viene del formulario de cotización, mostrar campos extra
if ($origen === 'Formulario de Cotizacion') {
    $email_content .= "Servicio de interés: $servicio\n";
    $email_content .= "Dirección/Condominio: $direccion\n";
}

$email_content .= "\nMensaje:\n$mensaje\n";

// Cabeceras del correo
$headers = "From: webmaster@proadministra.cl\r\n"; // Idealmente, usar un correo del mismo dominio
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Enviar correo
if (mail($to, $subject, $email_content, $headers)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "¡Gracias por tu mensaje! Nos pondremos en contacto pronto."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Oops! Hubo un error al enviar tu mensaje. (Error interno de PHP mail)"]);
}
?>
