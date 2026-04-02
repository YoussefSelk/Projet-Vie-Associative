<?php
declare(strict_types=1);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!class_exists(PHPMailer::class)) {
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/PHPMailer.php';
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/SMTP.php';
    require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/Exception.php';
}

function sendEmail($to, $subject, $message) {
    // Load from environment variables
    $smtp_host = Environment::get('SMTP_HOST', 'ssl0.ovh.net');
    $smtp_username = Environment::get('SMTP_USER', '');
    $smtp_password = Environment::get('SMTP_PASS', '');
    $smtp_port = (int) Environment::get('SMTP_PORT', 465);
    $smtp_from_name = Environment::get('SMTP_FROM_NAME', 'Projet Tech');

    // Validate email configuration
    if (empty($smtp_username) || empty($smtp_password)) {
        ErrorHandler::logError("Email configuration error: SMTP credentials not set", 'WARNING');
        return false;
    }

    // Validate recipient email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        ErrorHandler::logError("Invalid recipient email address: $to", 'WARNING', ['recipient' => $to]);
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        // En livraison, conserver un niveau de debug SMTP nul.
        $mail->SMTPDebug = 0;
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; 
        $mail->Port = $smtp_port;

        $mail->setFrom($smtp_username, $smtp_from_name);
        $mail->addAddress($to); 

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br(htmlspecialchars_decode($message));

        return $mail->send();
    } catch (Exception $e) {
        ErrorHandler::logError("Email sending failed: " . $mail->ErrorInfo, 'ERROR', [
            'recipient' => $to,
            'subject' => $subject
        ]);

        // Ne jamais propager d'information technique sensible a l'interface.
        return false;
    }
}

// Alias for backward compatibility
function envoyerMail($to, $subject, $message) {
    return sendEmail($to, $subject, $message);
}
