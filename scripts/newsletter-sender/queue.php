<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

// Newsletter-ID aus dem Request
$newsletterId = $data['id'];

// Newsletter-Daten holen
$newsletter = $db->getById('newsletter', $newsletterId);
if (!$newsletter) {
    echo json_encode(['success' => false, 'message' => 'Error while loading the newsletter!']);
    exit;
}

// Empfänger:innen ermitteln
$sql = "SELECT c.id
        FROM contact c
        JOIN contact_group_member gm ON c.id = gm.contact_id
        WHERE gm.contact_group_id = :contact_group_id AND c.user_id=:user_id";

$receivers = $db->getAllByQuery($sql, ['contact_group_id' => $newsletter['contact_group_id'],'user_id'=>$user_id]);

$added = 0;
$failed = 0;

// In die Queue-Tabelle einfügen
foreach ($receivers as $contact) {
    $insertData = [
        'contact_id' => $contact['id'],
        'newsletter_id' => $newsletterId,
        'sent' => 0
    ];
    
    $db->insertIgnore('newsletter_receiver', $insertData);
    $added++;
}

// Ergebnis zurückgeben
echo json_encode([
    'success' => true,
    'queued' => $added,
    'failed' => $failed,
    'newsletter_id' => $newsletterId
]);
?>