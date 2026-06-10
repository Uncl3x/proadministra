<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar y recoger datos
    $nombre = isset($_POST["nombre"]) ? strip_tags(trim($_POST["nombre"])) : "";
    $email = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : "";
    $telefono = isset($_POST["telefono"]) ? strip_tags(trim($_POST["telefono"])) : "";
    $mensaje = isset($_POST["mensaje"]) ? strip_tags(trim($_POST["mensaje"])) : "";
    $origen = isset($_POST["origen"]) ? strip_tags(trim($_POST["origen"])) : "Formulario Web";

    // Validar datos básicos
    if (empty($nombre) || empty($email) || empty($mensaje) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Por favor, completa todos los campos correctamente."]);
        exit;
    }

    // Configuración del correo
    $to = "contacto@proadministra.cl";
    $from = "contacto@proadministra.cl";
    $subject = $origen;

    $email_content = "Has recibido un nuevo mensaje desde el sitio web.\n\n";
    $email_content .= "Origen: $origen\n";
    $email_content .= "Nombre: $nombre\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Teléfono: $telefono\n\n";
    $email_content .= "Mensaje:\n$mensaje\n";

    $headers = "From: $from\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Enviar correo
    if (mail($to, $subject, $email_content, $headers)) {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "¡Gracias por tu mensaje! Nos pondremos en contacto pronto."]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Oops! Hubo un error al enviar tu mensaje."]);
    }
} else {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}
?>
