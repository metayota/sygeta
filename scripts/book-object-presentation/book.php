<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../mail-helper/mail-helper.php');  
    $customer = $db->get('customer',['user_id'=>$user_id]);
    $partner = $db->getById('partner',$customer['partner_id']);
    $customerContact = $db->getById('contact',$customer['contact_id']);
    $property = $db->getById('property',$data['property_id']);
    $partnerUser = $db->getById('user',$partner['user_id']);
    $partnerContact = $db->getById('contact',$partnerUser['contact_id']);
    $db->insert('object_presentation',['presentation_time'=>$data['date'].' '.$data['time'], 'property_id'=>$property['id'], 'customer_id'=>$customer['id'], 'partner_id'=>$customer['partner_id']]);

    $phpDatetimeFormat = translate('php_datetime_format');
    $formattedDate = date($phpDatetimeFormat, strtotime($data['date']));

    $vars = [
        'agent_name' => $partner['full_name'],
        'customer_firstname' => $customerContact['firstname'],
        'customer_lastname' => $customerContact['lastname'],
        'presentation_time' => $formattedDate,
        'address' => $property['address'],
        'city' => $property['city'],
        'agent_phone' => $partner['phone']
    ];

    sendMailHelper($customerContact['id'],'property_inspection_booked',$vars);

    sendMailHelper($partnerContact['id'],'property_inspection_booked_partner',$vars);

    success(translate('object_presentation_booked_success'));
?>