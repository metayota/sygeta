<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');

    if (!empty($data['contact_id'])) {
        checkReadPermissions('contact', $data['contact_id']);
        $contactID = $data['contact_id'];
    } else {
        $contactID = $_SESSION['loggedInUser']['contact_id'];
    }
    $documentsConfirmed = $db->get('documents_confirmed', ['contact_id'=>$contactID]);
    echo json_encode(['complete'=>!empty($documentsConfirmed)]);
?>