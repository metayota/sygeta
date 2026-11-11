<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $newsletter = $db->getById('newsletter',$data['id']);
    $newsletter['subject_translated'] = translate($newsletter['subject']);
    $newsletter['message_translated'] = translate($newsletter['message']);


    $sql = "SELECT c.*
    FROM contact c
    JOIN contact_group_member gm ON c.id = gm.contact_id
    WHERE gm.contact_group_id = :contact_group_id AND c.user_id=:user_id";
    $newsletter['receivers'] = $db->getAllByQuery($sql,['contact_group_id'=>$newsletter['contact_group_id'], 'user_id'=>$user_id]);



    echo json_encode($newsletter);
?>