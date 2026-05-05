<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Metodo non consentito.']);
    exit;
}

$nome      = trim($_POST['nome'] ?? '');
$cognome   = trim($_POST['cognome'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefono  = trim($_POST['telefono'] ?? '');
$posizione = trim($_POST['posizione'] ?? '');
$messaggio = trim($_POST['messaggio'] ?? '');

if (!$nome || !$cognome || !$email || !$posizione) {
    echo json_encode(['ok' => false, 'error' => 'Campi obbligatori mancanti.']);
    exit;
}

if (empty($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'CV obbligatorio. Seleziona un file PDF o DOCX.']);
    exit;
}

$allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

if (!in_array($_FILES['cv_file']['type'], $allowedTypes)) {
    echo json_encode(['ok' => false, 'error' => 'Solo PDF o DOCX accettati.']);
    exit;
}

if ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
    echo json_encode(['ok' => false, 'error' => 'File troppo grande. Massimo 5MB.']);
    exit;
}

$destinatario = 'info@artedileimpresa.it';
$subject = "CANDIDATURA: $nome $cognome - $posizione";
$boundary = md5(time() . rand());

$headers  = "From: Artedile Sito <info@artedileimpresa.it>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$messaggioEscaped = nl2br(htmlspecialchars($messaggio));

$textBody  = "Hai ricevuto una nuova candidatura dal sito web.\r\n\r\n";
$textBody .= "DETTAGLI CANDIDATO:\r\n";
$textBody .= "Nome: $nome $cognome\r\n";
$textBody .= "Posizione: $posizione\r\n";
$textBody .= "Email: $email\r\n";
$textBody .= "Telefono: " . ($telefono ?: '—') . "\r\n";
if ($messaggio) $textBody .= "\nMessaggio del candidato:\n$messaggio\r\n";
$textBody .= "\nIl CV è allegato alla mail.\r\n";

$htmlBody  = "<h2 style='color:#1A4B8C;font-family:sans-serif'>Nuova Candidatura — ARTEDILE</h2>";
$htmlBody .= "<table style='font-family:sans-serif;font-size:14px;border-collapse:collapse'>";
$htmlBody .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Nome</td><td>" . htmlspecialchars("$nome $cognome") . "</td></tr>";
$htmlBody .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Posizione</td><td>" . htmlspecialchars($posizione) . "</td></tr>";
$htmlBody .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Email</td><td><a href='mailto:$email'>$email</a></td></tr>";
$htmlBody .= "<tr><td style='padding:6px 16px 6px 0;color:#5A6A7A;font-weight:600'>Telefono</td><td>" . htmlspecialchars($telefono ?: '—') . "</td></tr>";
$htmlBody .= "</table>";
if ($messaggio) $htmlBody .= "<p style='font-family:sans-serif;margin-top:16px'><strong>Messaggio:</strong><br>$messaggioEscaped</p>";
$htmlBody .= "<p style='font-family:sans-serif;font-size:12px;color:#B0BEC5;margin-top:24px'>Il CV è allegato alla mail.</p>";

$fileContent = chunk_split(base64_encode(file_get_contents($_FILES['cv_file']['tmp_name'])));
$fileName = basename($_FILES['cv_file']['name']);
$fileType = $_FILES['cv_file']['type'];

$innerBoundary = md5($boundary . 'inner');

$body  = "--$boundary\r\n";
$body .= "Content-Type: multipart/alternative; boundary=\"$innerBoundary\"\r\n\r\n";
$body .= "--$innerBoundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n$textBody\r\n";
$body .= "--$innerBoundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n$htmlBody\r\n";
$body .= "--$innerBoundary--\r\n";
$body .= "--$boundary\r\n";
$body .= "Content-Type: $fileType; name=\"$fileName\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n";
$body .= "Content-Disposition: attachment; filename=\"$fileName\"\r\n\r\n";
$body .= "$fileContent\r\n";
$body .= "--$boundary--\r\n";

$inviato = mail($destinatario, $subject, $body, $headers);

if ($inviato) {
    echo json_encode(['ok' => true, 'message' => 'Candidatura inviata con successo! Verrai ricontattato presto.']);
} else {
    echo json_encode(['ok' => false, 'error' => "Errore nell'invio. Riprova più tardi."]);
}
