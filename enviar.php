<?php
// Permitir solicitudes desde cualquier origen (CORS) - Útil si se prueba desde otro dominio
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Manejar preflight request de CORS (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido"]);
    exit;
}

// Extraer variables desde POST normal
$origen = isset($_POST['origen']) ? $_POST['origen'] : 'Formulario Web';
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : 'No especificado';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
$servicio = isset($_POST['servicio']) ? trim($_POST['servicio']) : 'No especificado';
$direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : 'No especificada';

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
