<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../mail-contact-sender/mail-contact-sender.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  
  $event_id = $data['event_id'];
  $query = "SELECT c.* FROM contact c
            LEFT JOIN event_invitation wi ON c.id = wi.contact_id AND wi.event_id = :event_id
            WHERE wi.id IS NULL AND c.user_id=:user_id";
  $uninvited_contacts = $db->getAllByQuery($query, ['event_id' => $event_id,'user_id'=>$user_id]);
  echo json_encode($uninvited_contacts);
?>