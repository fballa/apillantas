<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;


// Requiere los archivos necesarios manualmente
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Servidor SMTP (ej. Gmail)
    $mail->SMTPAuth   = true;
    $mail->Username   = 'franklinballadarespaypal@gmail.com'; // Usuario SMTP
    $mail->Password   = 'ydubswgepdkxmhvr'; // Contraseña
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // O ENCRYPTION_SMTPS [3]
    $mail->Port       = 587; // O 465

    // Remitente y destinatario
    $mail->setFrom('franklinballadarespaypal@gmail.com', 'franklin paypal');
    $mail->addAddress('frankball4@yahoo.es');

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Test phpmailer';
    $mail->Body    = 'Este es el cuerpo en HTML <b>negrita</b>';

    $mail->send();
    echo 'Mensaje enviado';
} catch (Exception $e) {
    echo "Mensaje no enviado. Error: {$mail->ErrorInfo}";
}

?>
