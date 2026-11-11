<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

    include_once('../access-check/access-check.php');
    checkWritePermissions('contact', $data['contact_id']);

    $dealLostContactType = $db->getByName('contact_type', 'deal_lost');

    $customer = $db->get('customer',['contact_id'=>$data['contact_id']]);
    if ($customer) {
        $data['customer_id'] = $customer['id'];
    }

    $db->update('contact',['contact_type_id'=>$dealLostContactType['id']], ['id'=>$customer['contact_id']]);
    $db->update('customer',['lost_deal'=>true], ['id'=>$customer['id']]);

    $db->insert('lost_deal',$data);
    success('msg_marked_deal_lost');
?>