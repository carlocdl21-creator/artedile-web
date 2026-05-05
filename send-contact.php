<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Metodo non consentito.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$nome     = trim($input['nome'] ?? '');
$email    = trim($input['email'] ?? '');
$azienda  = trim($input['azienda'] ?? '');
$oggetto  = trim($input['oggetto'] ?? '');
$messaggio = trim($input['messaggio'] ?? '');

if (!$nome || !$email) {
    echo json_encode(['ok' => false, 'error' => 'Nome ed email sono obbligatori.']);
    exit;
}

$destinatario = 'carlocdl21@gmail.com';
$subject = 'Nuovo Contatto: ' . ($oggetto ?: 'Informazioni') . ' - ' . ($azienda ?: $nome);

$boundary = md5(time());

$headers  = "From: Artedile Sito <info@artedileimpresa.it>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

$messaggioEscaped = nl2br(htmlspecialchars($messaggio));

$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= "Hai ricevuto un nuovo messaggio dal sito web.\r\n\r\n";
$body .= "Nome: $nome\r\n";
$body .= "Email: $email\r\n";
$body .= "Azienda: " . ($azienda ?: '—') . "\r\n";
$body .= "Oggetto: " . ($oggetto ?: '—') . "\r\n\r\n";
$body .= "Messaggio:\r\n$messaggio\r\n";
$body .= "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$body .= "<h2 style='color:#1A4B8C;font-family:sans-serif'>Nuovo Messaggio — ARTEDILE</h2>";
$body .= "<table style='font-family:sans-serif;font-size:14px;border-collapse:collapse'>";
$body .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Nome</td><td>" . htmlspecialchars($nome) . "</td></tr>";
$body .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Email</td><td><a href='mailto:$email'>$email</a></td></tr>";
$body .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Azienda</td><td>" . htmlspecialchars($azienda ?: '—') . "</td></tr>";
$body .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Oggetto</td><td>" . htmlspecialchars($oggetto ?: '—') . "</td></tr>";
$body .= "</table>";
$body .= "<p style='font-family:sans-serif;margin-top:16px'><strong>Messaggio:</strong><br>$messaggioEscaped</p>";
$body .= "<p style='font-family:sans-serif;font-size:12px;color:#B0BEC5;margin-top:24px'>Messaggio ricevuto dal sito artedileimpresa.it</p>";
$body .= "--$boundary--\r\n";

$inviato = mail($destinatario, $subject, $body, $headers);

if ($inviato) {
    echo json_encode(['ok' => true, 'message' => 'Messaggio inviato con successo!']);
} else {
    echo json_encode(['ok' => false, 'error' => "Errore nell'invio. Riprova più tardi."]);
}
