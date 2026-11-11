<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');

    if (!empty($data['contact_id'])) {         // TODO: CHECK ACCESS
        checkWritePermissions('contact', $data['contact_id']);
        $customer = $db->getById('contact',$data['contact_id']);
        $data['user_id'] = $customer['user_id'];
    } else {
        $data['contact_id'] = $_SESSION['loggedInUser']['contact_id'];
    }
    $data['user_id'] = $user_id;
    $db->insert('document',$data);
    success('document_added_successfully');
?>