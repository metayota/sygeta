<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

require_once('../mail-sender/mail-sender.php');

use PHPMailer\PHPMailer\Exception;

// Newsletter-ID aus dem Request
$newsletterId = $data['id'];

// Newsletter-Daten holen
$newsletter = $db->getById('newsletter', $newsletterId);
if (!$newsletter) {
    echo json_encode(['success' => false, 'message' => 'Error while loading the newsletter!']);
    exit;
}

// Betreff und Text übersetzen
$subject = translate($newsletter['subject']);
$content = $newsletter['message'];

// Empfänger:innen ermitteln
$sql = "SELECT c.*
        FROM contact c
        JOIN contact_group_member gm ON c.id = gm.contact_id
        WHERE gm.contact_group_id = :contact_group_id";

$receivers = $db->getAllByQuery($sql, ['contact_group_id' => $newsletter['contact_group_id']]);

$sent = 0;
$failed = 0;
$errors = [];

foreach ($receivers as $contact) {
    if (empty($contact['email'])) {
        $failed++;
        continue;
    }

    try {
        $mail = new MailSender();
        $mail->addReceiver($contact['email']);
        $mail->setSubject($subject);
        $mail->setHTML($content);
        $mail->send();
        $sent++;
    } catch (Exception $e) {
        $failed++;
        $errors[] = [
            'email' => $contact['email'],
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