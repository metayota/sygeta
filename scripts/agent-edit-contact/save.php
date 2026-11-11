<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../translation-service/translation-service.php');
    include_once('../create-contact/lib.php');
    $user = $db->getById('user', $user_id);
    if (!empty($user['contact_id'])) {
        $contactID = $db->getColumnById('contact','id', $user['contact_id']);
    }
    if (empty($contactID)) {
        $data['user_id'] = $user_id;
        $contactID = createContact($data);
        $db->update('user', ['contact_id'=>$contactID], $user_id);
    } else {
        $db->update('contact', $data, $contactID);
    }
    success('msg_saved')
?>