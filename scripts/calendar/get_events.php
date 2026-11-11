<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../format-text/format-text.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
include('../calendar/lib.php');
include_once('../contacts-lib/contacts-lib.php');

$contact_id = $db->getColumnById('user','contact_id', $user_id);
$timezone = getTimezoneForContact($contact_id);
$eventForUser = getEventsForUser($user_id, $timezone);
echo json_encode($eventForUser);
?>