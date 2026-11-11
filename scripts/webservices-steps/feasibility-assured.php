<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../translation-service/translation-service.php');   
    $contact = $db->getById('contact', $data['contact_id']);
    $customer = $db->get('customer',['contact_id'=>$contact['id']]);

    $selfDisclosure = $db->get('self_disclosure',['customer_id'=>$customer['id']]);
    $db->update('self_disclosure',['feasibility_assured'=>1],['id'=>$selfDisclosure['id']]);

    $sender = new MailContactSender();
    $sender->addContact($contact['id']);
    $sender->setTemplate('feasibility_assured');
    $sender->send();

    success('feasibility_assured_message');
?>