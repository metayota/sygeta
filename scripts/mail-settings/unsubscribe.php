<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $contact = $db->get( 'contact', ['access_key' => $get['access_key']] );
    $typeName = $get['mail_type'];
    if (!empty($contact)) {
        $db->insertIgnore('mail_setting',['contact_id'=>$contact['id'],'type_name'=>$typeName,'receive_mail'=>0]);
    }
    
    header('Location:/page/mail-settings-changed');
?>