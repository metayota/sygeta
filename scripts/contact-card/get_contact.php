<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    checkReadPermissions('contact', $data['id']);
    $contact = $db->getById('contact', $data['id']);
    $contactType = $db->getById('contact_type', $contact['contact_type_id']);
    if (!empty($contactType)) {
        $contact['contact_type'] = ['title_translated'=> translate($contactType['title'])];
    }
    $contactOwner = $db->getByQuery('SELECT u.username, c.full_name FROM contact c LEFT JOIN user u ON u.id=c.user_id WHERE u.id=:user_id', ['user_id'=>$contact['user_id']]);
    $contact['owner'] = $contactOwner;
    echo json_encode($contact);
?>