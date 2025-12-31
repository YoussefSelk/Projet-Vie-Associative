<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/PHPMailer.php';
require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/SMTP.php';
require_once CONFIG_PATH . '/../assets/lib/PHPMailer-master/src/Exception.php';

function sendEmail($to, $subject, $message) {
    // Load from environment variables
    $smtp_host = Environment::get('SMTP_HOST', 'ssl0.ovh.net');
    $smtp_username = Environment::get('SMTP_USER', '');
    $smtp_password = Environment::get('SMTP_PASS', '');
    $smtp_port = (int) Environment::get('SMTP_PORT', 465);
    $smtp_from_name = Environment::get('SMTP_FROM_NAME', 'Projet Tech');

    // Validate email configuration
    if (empty($smtp_username) || empty($smtp_password)) {
        error_log("Email configuration error: SMTP credentials not set");
        return false;
    }

    // Validate recipient email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid recipient email address: $to");
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
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
        error_log("Email sending failed: " . $mail->ErrorInfo);
        
        if (Environment::isProduction()) {
            return false;
        }
        return "Erreur : " . $mail->ErrorInfo;
    }
}

// Alias for backward compatibility
function envoyerMail($to, $subject, $message) {
    return sendEmail($to, $subject, $message);
}
