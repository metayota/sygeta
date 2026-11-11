<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   

$contact = $db->get('contact', ['access_key'=>$data['access_key']]);

$sql = "SELECT COALESCE(ms.receive_mail, 1) AS receive_mail, 
               tr.translation AS title_translated, 
               mt.name AS type_name
        FROM mail_template mt
        LEFT JOIN mail_setting ms ON mt.name = ms.type_name AND ms.contact_id = :contact_id
        LEFT JOIN translation tr ON tr.translation_key = mt.title AND tr.language = :language";

$mailSettings = $db->getAllByQuery($sql, [
    'contact_id' => $contact['id'],
    'language' => $GLOBALS['language']
]);

echo json_encode($mailSettings);
?>