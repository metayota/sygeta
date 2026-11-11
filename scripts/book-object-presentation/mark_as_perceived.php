<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');   

    $contact = $db->getById('contact',$data['contact_id']);
    $customer = $db->get('customer',['contact_id'=>$contact['id']]);
    $partner = $db->getById('partner',$customer['partner_id']);

    $db->update('object_presentation',['attended'=>1],['customer_id'=>$customer['id']]);

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $domain = $_SERVER['HTTP_HOST'];
    $appURL = $protocol . "://" . $domain;

    $vars = [
        'agent_name' => $partner['full_name'],
        'agent_phone' => $partner['phone'],
        'app_url' => $appURL
    ];
    sendMailHelper($contact['id'],'upload_documents',$vars);
    success(translate('object_presentation_marked_attended'));
    
?>