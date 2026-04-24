<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. DESTINATARIO
    $destinatario = "info@artedileimpresa.it";
    
    // 2. RECUPERO DATI DAL FORM
    $nome = strip_tags($_POST['nome']);
    $cognome = strip_tags($_POST['cognome']);
    $email_mittente = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    $oggetto = "Nuova Candidatura Sito Artedile: $nome $cognome";

    // 3. GESTIONE ALLEGATO
    $file_nome = $_FILES['cv_file']['name'];
    $file_tipo = $_FILES['cv_file']['type'];
    $file_temp = $_FILES['cv_file']['tmp_name'];

    // Lettura e codifica del file
    $handle = fopen($file_temp, "r");
    $content = fread($handle, filesize($file_temp));
    fclose($handle);
    $encoded_content = chunk_split(base64_encode($content));

    // 4. CREAZIONE INTESTAZIONI EMAIL (Multipart per allegato)
    $boundary = md5(time());
    
    $header = "From: Artedile Sito <noreply@artedileimpresa.it>\r\n";
    $header .= "Reply-To: " . $email_mittente . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\r\n";

    // Messaggio testuale
    $body = "--" . $boundary . "\r\n";
    $body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= "Hai ricevuto una nuova candidatura dal sito web.\n\n";
    $body .= "Dettagli Candidato:\n";
    $body .= "Nome: $nome\n";
    $body .= "Cognome: $cognome\n";
    $body .= "Email: $email_mittente\n\n";
    $body .= "Il file CV è allegato alla presente mail.\r\n";

    // Codifica Allegato
    $body .= "--" . $boundary . "\r\n";
    $body .= "Content-Type: $file_tipo; name=\"" . $file_nome . "\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"" . $file_nome . "\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= $encoded_content . "\r\n";
    $body .= "--" . $boundary . "--";

    // 5. INVIO
    if (mail($destinatario, $oggetto, $body, $header)) {
        // Se l'invio riesce, mostra un messaggio e torna alla home
        echo "<script>
                alert('Candidatura inviata con successo! Verrai ricontattato presto.');
                window.location.href='index.html'; 
              </script>";
    } else {
        echo "Si è verificato un errore nell'invio della candidatura. Riprova più tardi.";
    }
} else {
    // Se qualcuno prova ad accedere al file direttamente
    header("Location: index.html");
}
?>