<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');  

use PHPMailer\PHPMailer\Exception;

// Anzahl der zu versendenden E-Mails pro Durchlauf
$batchSize = 50;

// Query 1: Alle Newsletter mit ungesendeten Nachrichten laden
$newsletterSql = "SELECT DISTINCT n.*
                  FROM newsletter n
                  LEFT JOIN newsletter_receiver nr ON n.id = nr.newsletter_id
                  WHERE nr.sent = 0";

$newsletters = $db->getAllByQuery($newsletterSql);

$sent = 0;
$failed = 0;
$errors = [];

foreach ($newsletters as $newsletter) {
    // Query 2: Kontakte für diesen Newsletter laden
    $contactsSql = "SELECT nr.id as receiver_id, nr.contact_id, c.*
                    FROM newsletter_receiver nr
                    LEFT JOIN contact c ON nr.contact_id = c.id
                    WHERE nr.newsletter_id = :newsletter_id 
                    AND nr.sent = 0
                    ORDER BY RAND()
                    LIMIT $batchSize";

    $contacts = $db->getAllByQuery($contactsSql, [
        'newsletter_id' => $newsletter['id']
    ]);

    // Betreff einmal übersetzen für diesen Newsletter
    $subject = $newsletter['subject'];
    $content = $newsletter['message'];

    foreach ($contacts as $contact) {
        if (empty($contact['email'])) {
            // Als fehlgeschlagen markieren
            $db->update('newsletter_receiver', ['sent' => 2], $contact['receiver_id']);
            $failed++;
            continue;
        }

        try {
            $existingReceiver = $db->getById('newsletter_receiver', $contact['receiver_id']);
            if ($existingReceiver['sent'] == 1) {
                continue;
            }
            $db->update('newsletter_receiver', ['sent' => 1], $contact['receiver_id']);
            $sender = new MailContactSender();
            $sender->addContact($contact['contact_id']);
            $sender->setTemplate('newsletter', ['subject' => translateLanguage($contact['language'],$subject), 'title' => translateLanguage($contact['language'],$subject), 'content' => translateLanguage($contact['language'],$content)]);
            $sender->send();
            
            // Als gesendet markieren
            $sent++;
        } catch (Exception $e) {
            // Als fehlgeschlagen markieren
            $db->update('newsletter_receiver', ['sent' => 2], $contact['receiver_id']);
            $failed++;
            $errors[] = [
                'email' => $contact['email'],
                'error' => $e->getMessage()
            ];
        }
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