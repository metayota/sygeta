<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $contact = $db->get('contact', ['access_key'=>$data['access_key']]);

    if ($data['value']) {
        $db->delete('mail_setting',['contact_id'=>$contact['id'],'type_name'=>$data['type_name']]);
    } else {
        $db->insertIgnore('mail_setting',['contact_id'=>$contact['id'],'type_name'=>$data['type_name'],'receive_mail'=>0]);
    }
    success('setting_saved');
?>