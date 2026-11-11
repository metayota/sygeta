<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   include_once('../php-mailer/php-mailer.php');  
  $webinar_id = $data['webinar_id'];
  $query = "SELECT c.* FROM contact c
            LEFT JOIN webinar_invitation wi ON c.id = wi.contact_id AND wi.webinar_id = :webinar_id
            WHERE wi.id IS NULL AND c.user_id=:user_id";
  $uninvited_contacts = $db->getAllByQuery($query, ['webinar_id' => $webinar_id,'user_id'=>$user_id]);
  echo json_encode($uninvited_contacts);
?>