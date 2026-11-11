<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
    $sql = "SELECT mi.* FROM meeting_invitation mi LEFT JOIN contact c ON c.id=mi.receiver_contact_id WHERE c.access_key=:access_key and mi.id=:meeting_invitation_id";
    echo json_encode($db->getByQuery($sql, $data));
?>