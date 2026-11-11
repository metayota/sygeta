<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

    // Hole Partner-ID für diesen User
    $partner = $db->get('partner', ['user_id' => $user_id]);
    if (!$partner) {
        throw new \Exception('Partner not found');
    }
    $partnerID = $partner['id'];

$sql = "SELECT 
    car.*, 
    actTT.translation as title_translated,
    actDT.translation as description_translated,
    at.title AS activity_type_title, 
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
    p.id = :partner_id
ORDER BY car.id DESC";

$activities = $db->getAllByQuery($sql, ['language' => $GLOBALS['language'], 'partner_id' => $partnerID]);
$dateFormat = translate('php_date_format');

foreach ($activities as &$activity) {
    $activity['activity_date_formatted'] = date($dateFormat, strtotime($activity['activity_date']));
    if (empty($activity['partner_full_name'])) {
        $activity['partner_full_name'] = 'Admin';
    }
}

echo json_encode($activities);

?>