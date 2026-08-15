<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// ─── CONFIGURACIÓN SMTP ────────────────────────────────────────────────────
// 1. Activa la verificación en 2 pasos en tu cuenta Gmail.
// 2. Ve a: https://myaccount.google.com/apppasswords
// 3. Genera una "Contraseña de aplicación" y pégala abajo.
define('SMTP_USER', 'Freddypimpns@gmail.com'); // Tu correo Gmail
define('SMTP_PASS', 'bltrgsmqcqrhxdgv');     // App Password de Google (16 caracteres)
define('MAIL_TO', 'Freddypimpns@gmail.com');  // Destinatario
// ──────────────────────────────────────────────────────────────────────────

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Sanitizar y validar campos
$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
$phone = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

if (empty($name) || empty($phone) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
    exit;
}

// ─── ENVÍO CON PHPMAILER ───────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // Servidor SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom(SMTP_USER, 'PayrollTask');
    $mail->addAddress(MAIL_TO, 'PayrollTask Admin');
    $mail->addReplyTo($email, $name);

    // Contenido HTML
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo mensaje de contacto - PayrollTask';
    $mail->Body = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
            .header { background: linear-gradient(135deg,#6c63ff,#4f46e5); padding: 30px; text-align: center; }
            .header h1 { color: #fff; margin: 0; font-size: 24px; }
            .header p  { color: rgba(255,255,255,.8); margin: 6px 0 0; font-size: 14px; }
            .body { padding: 30px; }
            .field { margin-bottom: 20px; }
            .field label { display: block; font-size: 12px; font-weight: bold; color: #6c63ff; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
            .field p { margin: 0; font-size: 15px; color: #333; background: #f9f9f9; padding: 10px 14px; border-radius: 6px; border-left: 3px solid #6c63ff; }
            .footer { background: #f4f4f4; padding: 16px 30px; text-align: center; font-size: 12px; color: #999; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📬 Nuevo Mensaje de Contacto</h1>
                <p>PayrollTask Platform</p>
            </div>
            <div class='body'>
                <div class='field'><label>Nombre</label><p>{$name}</p></div>
                <div class='field'><label>Teléfono</label><p>{$phone}</p></div>
                <div class='field'><label>Correo Electrónico</label><p>{$email}</p></div>
                <div class='field'><label>Mensaje</label><p>{$message}</p></div>
            </div>
            <div class='footer'>
                Mensaje enviado desde el formulario de contacto de PayrollTask.
            </div>
        </div>
    </body>
    </html>";

    $mail->AltBody = "Nombre: {$name}\nTeléfono: {$phone}\nCorreo: {$email}\nMensaje: {$message}";

    $mail->send();
    echo json_encode(['success' => true, 'message' => '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el mensaje: ' . $mail->ErrorInfo
    ]);
}
?>