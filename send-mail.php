<?php
date_default_timezone_set('Europe/Zurich');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$name     = trim(strip_tags($_POST['name']     ?? ''));
$business = trim(strip_tags($_POST['business'] ?? ''));
$email    = trim(strip_tags($_POST['email']    ?? ''));
$phone    = trim(strip_tags($_POST['phone']    ?? ''));
$type     = trim(strip_tags($_POST['type']     ?? ''));
$message  = trim(strip_tags($_POST['message']  ?? ''));

if (!$name || !$business || !$email) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Pflichtfelder fehlen']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail-Adresse']);
    exit;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'vendor/autoload.php nicht gefunden']);
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'email';
    $mail->Password   = 'pw';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->Timeout    = 15;

    $mail->setFrom('info@vithout.ch', 'Vithout');
    $mail->addAddress('info@vithout.ch', 'Vithout');
    $mail->addAddress('denis.marian0290@gmail.com', 'Vithout');
    $mail->addReplyTo($email, $name);

    $mail->Subject = 'Neue Anfrage von ' . $name . ' - ' . $business;

    $body  = "Neue Anfrage ueber das Kontaktformular auf vithout.ch\n";
    $body .= str_repeat('-', 50) . "\n\n";
    $body .= 'Name:              ' . $name     . "\n";
    $body .= 'Betrieb:           ' . $business . "\n";
    $body .= 'E-Mail:            ' . $email    . "\n";
    if ($phone)   { $body .= 'Telefon:           ' . $phone   . "\n"; }
    if ($type)    { $body .= 'Art des Betriebs:  ' . $type    . "\n"; }
    if ($message) { $body .= "\nNachricht:\n"       . $message . "\n"; }
    $body .= "\nGesendet am: " . date('d.m.Y H:i') . " Uhr\n";

    $mail->Body = $body;
    $mail->send();

    echo json_encode(['ok' => true]);

} catch (MailerException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Mailer: ' . $mail->ErrorInfo]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
