<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
include('../mail-sender/mail-sender.php');
include_once('../contacts-lib/contacts-lib.php');

$event_id = $data['event_id'];
$contact_ids = $data['contact_ids'];

$event = $db->get('event', ['id' => $event_id]);
$eventType = $db->getByName('event_type', $event['event_type']);

// 1. Nächstes Datum aus event_date abfragen
$nextEventDate = $db->getByQuery("SELECT * FROM event_date WHERE event_id = :event_id AND start_date > NOW() ORDER BY start_date ASC LIMIT 1", ['event_id'=>$event_id]);

if (empty($nextEventDate)) {
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
    $contact = $db->get('contact', ['id' => $contact_id, 'user_id'=>$user_id]);
    if (empty($contact)) {
        continue;
    }
    
    // Get contact's timezone
    $timezone = getTimezoneForContact($contact['id']);
    
    // 2. Datum und Zeit in Kontaktsprache übersetzen
    $date_format = translateLanguage($contact['language'], 'php_date_format');
    $time_format = translateLanguage($contact['language'], 'php_time_format');
    
    // Convert UTC datetime to contact's timezone for display
    $utcDate = new \DateTime($nextEventDate['start_date'], new \DateTimeZone('UTC'));
    $utcDate->setTimezone(new \DateTimeZone($timezone));
    
    $event_date = $utcDate->format($date_format);
    $event_time = $utcDate->format($time_format);

    $invitationCode = generate_unique_alphanumeric($contact['id']);
    $appSettings = $db->get('app_settings');
    
    $replacements = [
        'event_name' => translateLanguage($contact['language'], $event['title']),
        'event_description' => translateLanguage($contact['language'],$event['description']),
        'event_date' => $event_date,
        'event_time' => $event_time,
        'inviter_name' => $inviter_name,
        'firstname' => $contact['firstname'],
        'invitation_link' => $appSettings['app_url'].$contact['language'].'/event-invitation/'.$event['name'].'/'.$invitationCode
    ];

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate($eventType['mail_template_invitation'],$replacements);
    $sender->send();
  
    $db->insert('event_invitation', ['event_id' => $event_id, 'contact_id' => $contact_id,'invitation_code'=>$invitationCode]);
}

success('event_invitations_sent');
?>