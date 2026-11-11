<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    include_once('../access-check/access-check.php');
    checkReadPermissions('customer', $data['id']);
    $customer = $db->getById('customer',$data['id']);
    $contact = $db->getById('contact',$customer['contact_id']);
    $partner = $db->getById('partner',$customer['partner_id']);
    $partnerContact = $db->getById('contact',$partner['contact_id']);
    $result = [];
    $result['customer'] = $customer;
    $result['contact'] = $contact;
    $result['partner'] = $partner;
    $result['partner_contact'] = $partnerContact;
    echo json_encode($result);
?>