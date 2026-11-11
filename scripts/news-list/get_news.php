<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
requireAccess('db.table.get_all','news');
$news = $db->getAll('news');
$dateFormat = translate('php_date_format');
foreach($news as &$newsItem) {
    $newsItem['title'] = translate($newsItem['title']);
    $newsItem['teaser'] = translate($newsItem['teaser']);
    $newsItem['publish_date_formatted'] =  date($dateFormat, strtotime($newsItem['publish_date']));
}


echo json_encode($news);
?>