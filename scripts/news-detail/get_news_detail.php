<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    include_once('../access-check/access-check.php');
    $id = $data['id'];
    checkReadPermissions('news', $id);

    $news = $db->get('news', ['id' => $id]);
    $dateFormat = translate('php_date_format');
    $news['title'] = translate($news['title']);
    $news['teaser'] = translate($news['teaser']);
    $news['text'] = translate($news['text']);
    $news['publish_date_formatted'] =  date($dateFormat, strtotime($news['publish_date']));
    echo json_encode($news);
?>