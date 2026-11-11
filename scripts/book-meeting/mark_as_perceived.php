<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   
    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);
    $contact = $db->getById('contact',$data['contact_id']);
    $partner = $db->getById('partner',$customer['partner_id']);
    $meetingType = $db->getByName('meeting_type', $data['meeting_type']);

    $meetingParticipant = $db->getByQuery('SELECT mp.* FROM meeting m LEFT JOIN meeting_participant mp ON mp.meeting_id = m.id WHERE mp.contact_id=:contact_id AND m.meeting_type_id=:meeting_type_id', ['meeting_type_id'=>$meetingType['id'], 'contact_id'=>$contact['id']]);
    $meetingParticipant['attended'] = 1;
    $db->update('meeting_participant', $meetingParticipant);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $appURL = $protocol . "://" . $domain;


    success('meeting_marked_attended');
    
?>