<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$sql = "SELECT 
    car.*, 
    at.title AS activity_type_title, 
    actTT.translation as title_translated,
    actDT.translation as description_translated,
    t.translation AS activity_type_translation, 
    p.full_name AS partner_full_name
FROM 
    contact_activity car
JOIN 
    activity_type at ON car.activity_type_id = at.id
LEFT JOIN 
    translation t ON at.title = t.translation_key AND t.language = :language
LEFT JOIN 
    translation  actTT ON car.title = actTT.translation_key AND actTT.language = :language
LEFT JOIN 
    translation  actDT ON car.description = actDT.translation_key AND actDT.language = :language
LEFT JOIN 
    partner p ON car.partner_id = p.id
WHERE 
    car.contact_id = :contact_id
ORDER BY car.id DESC";

$activities = $db->getAllByQuery($sql, ['language' => $GLOBALS['language'], 'contact_id' => $data['contact_id']]);
$dateFormat = translate('php_date_format');

foreach ($activities as &$activity) {
    $activity['activity_date_formatted'] = date($dateFormat, strtotime($activity['activity_date']));
    if (empty($activity['partner_full_name'])) {
        $activity['partner_full_name'] = 'Admin';
    }
    if (empty($activity['title'])) {
        $activity['title'] = $activity['activity_type_translation'];
    }
}

echo json_encode($activities);

?>