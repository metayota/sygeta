<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

require_once('../mail-sender/mail-sender.php');

use PHPMailer\PHPMailer\Exception;

// Anzahl der zu versendenden E-Mails pro Durchlauf
$batchSize = 50;

// Ungesendete Einträge abfragen
$sql = "SELECT nr.id, nr.contact_id, nr.newsletter_id, c.email, n.subject, n.message 
        FROM newsletter_receiver nr
        JOIN contact c ON nr.contact_id = c.id
        JOIN newsletter n ON nr.newsletter_id = n.id
        WHERE nr.sent = 0
        LIMIT 50";

$queue = $db->getAllByQuery($sql);

$sent = 0;
$failed = 0;
$errors = [];

foreach ($queue as $item) {
    if (empty($item['email'])) {
        // Als fehlgeschlagen markieren
        $db->update('newsletter_receiver', ['sent' => 2], $item['id']);
        $failed++;
        continue;
    }

    try {
        // Betreff übersetzen
        $subject = translate($item['subject']);
        $content = $item['message'];

       // $db->update('newsletter_receiver', ['sent' => 1], $item['id']);
        echo $item['email'];
        $mail = new MailSender();
        $mail->addReceiver($item['email']);
        $mail->setSubject($subject);
        $mail->setHTML($content);
       // $mail->send();
        
        // Als gesendet markieren
        $sent++;
    } catch (Exception $e) {
        // Als fehlgeschlagen markieren
        $db->update('newsletter_receiver', ['sent' => 2], $item['id']);
        $failed++;
        $errors[] = [
            'email' => $item['email'],
            'error' => $e->getMessage()
        ];
    }
}

// Ergebnis zurückgeben
echo json_encode([
    'success' => true,
    'sent' => $sent,
    'failed' => $failed,
    'errors' => $errors
]);
?>