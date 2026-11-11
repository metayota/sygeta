<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../slugify/slugify.php');  
include_once('../jitsi-lib/jitsi-lib.php');

$meetingType = $db->getById('meeting_type', $meeting['meeting_type_id']);

$meetingTypeEnglish = translateLanguage('en', $meetingType['title']);
$roomName = $meetingTypeEnglish.$meeting['id'];

$contact = $db->getById('contact', $contact['id']);
$partner = $db->getById('partner', $contact['partner_id']);

$moderatorLink = createJitsiMeetingShortlink($roomName, $partner['full_name'], true);
$userLink = createJitsiMeetingShortlink($roomName, $contact['full_name'], false);


return ['video_link_moderator' => $moderatorLink, 'video_link_user' => $userLink];

?>