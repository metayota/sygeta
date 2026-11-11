<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../php-mailer/php-mailer.php');  
$event_id = $data['event_id'];
$contact_ids = $data['contact_ids'];

$event = $db->get('event', ['id' => $event_id]);

// 1. Nächstes Datum aus event_date abfragen
$nextWebinarDate = $db->getByQuery("SELECT * FROM event_date WHERE event_id = :event_id AND start_date > NOW() ORDER BY start_date ASC LIMIT 1", ['event_id'=>$event_id]);

if (empty($nextWebinarDate)) {
    msg(translate('msg_no_event_planned'));
}

// 4. Inviter Name ermitteln
$user = $db->getById('user', $user_id);
$inviterContact = $db->getById('contact',$user['contact_id']);
$inviter_name = $inviterContact['firstname'] . ' ' . $inviterContact['lastname'];

function generate_unique_alphanumeric($id) {
    $id = ($id < 1 || $id > 1000000) ? 1 : $id; // Sicherheitscheck
    
    $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    $base = strlen($chars);
    
    $a = 1664525;
    $b = 1013904223;
    $m = pow(2, 32);
    
    $hash = ($a * $id + $b) % $m;
    $result = '';
    
    while ($hash > 0 || strlen($result) < 6) {
        $result = $chars[$hash % $base] . $result;
        $hash = floor($hash / $base);
    }
    
    return $result;
}



foreach ($contact_ids as $contact_id) {
    $contact = $db->get('contact', ['id' => $contact_id]);
    
    // 2. Datum und Zeit in Kontaktsprache übersetzen
    $date_format = translateLanguage($contact['language'], 'php_date_only_format');
    $time_format = translateLanguage($contact['language'], 'php_time_only_format');
    
    $event_date = date($date_format, strtotime($nextWebinarDate['start_date']));
    $event_time = date($time_format, strtotime($nextWebinarDate['start_date']));
    
    
    // 3. Text und Betreff vorbereiten und Platzhalter ersetzen
    $invitation_text = translateLanguage($contact['language'], $event['invitation_text']);
    $subject = translateLanguage($contact['language'], $event['invitation_subject']);

    $invitationCode = generate_unique_alphanumeric($contact['id']);
    $appSettings = $db->get('app_settings');
    
    $replacements = [
        '[event_date]' => $event_date,
        '[event_time]' => $event_time,
        '[inviter_name]' => $inviter_name,
        '[firstname]' => $contact['firstname'],
        '[event_link]' => $nextWebinarDate['event_link'],
        '[invitation_link]' => $appSettings['app_url'].$contact['language'].'/event-invitation/'.$event['name'].'/'.$invitationCode
    ];
    
    $invitation_text = strtr($invitation_text, $replacements);
    $subject = strtr($subject, $replacements);
    


    sendMail($contact['email'], $subject, $invitation_text);
    
    $db->insert('event_invitation', ['event_id' => $event_id, 'contact_id' => $contact_id,'invitation_code'=>$invitationCode]);
}

success(translate('event_invitations_sent'));
?>