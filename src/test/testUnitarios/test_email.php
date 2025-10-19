<?php
// Cargar el autoloader y Environment
require_once __DIR__ . '/../../utils/Environment.php';

// Inicializar Environment
App\Utils\Environment::init();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use App\Utils\Environment;

// Incluye PHPMailer desde la carpeta lib
require __DIR__ . '/../../../lib/PHPMailer/src/Exception.php';
require __DIR__ . '/../../../lib/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../../lib/PHPMailer/src/SMTP.php';

// Configuración SMTP
$mail = new PHPMailer(true);

try {
    // Habilitar modo debug para ver detalles
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "Debug level $level: $str<br>\n";
    };

    $mail->isSMTP();
    $mail->Host       = Environment::get('SMTP_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = Environment::get('SMTP_USER');
    $mail->Password   = Environment::get('SMTP_PASS');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int)Environment::get('SMTP_PORT');
    
    // Desactivar verificación SSL en desarrollo
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    echo "<h3>Configuración SMTP:</h3>";
    echo "Host: " . $mail->Host . "<br>";
    echo "Port: " . $mail->Port . "<br>";
    echo "Username: " . $mail->Username . "<br>";
    echo "Password: " . (Environment::get('SMTP_PASS') ? '****' : 'NO CONFIGURADA') . "<br><br>";

    // Remitente y destinatario
    $mail->setFrom(
        Environment::get('SMTP_FROM_EMAIL'),
        Environment::get('SMTP_FROM_NAME')
    );
    $mail->addAddress('daniel.sanchez.ruiz.1991@gmail.com', 'Daniel Sanchez');

    // Contenido
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Prueba SMTP desde PHP - Sistema de Herramientas';
    $mail->Body    = '<h3>Envío de prueba correcto ✅</h3><p>Este es un correo de prueba desde el sistema de gestión de herramientas.</p>';
    $mail->AltBody = 'Envío de prueba correcto. Este es un correo de prueba desde el sistema de gestión de herramientas.';

    $mail->send();
    echo "<h2 style='color: green;'>✅ Correo enviado correctamente.</h2>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error al enviar correo</h2>";
    echo "<p><strong>Error:</strong> {$mail->ErrorInfo}</p>";
    echo "<p><strong>Excepción:</strong> " . $e->getMessage() . "</p>";
}
