<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');  
    $useDB = $db;
    if (getConfig('main_server')) {
        $useDB = $serverDB;
    }  
    $language = $GLOBALS['language'];
    $rows = $useDB->getAllByQuery("SELECT t.* FROM translation_area ta INNER JOIN translation t ON t.translation_key = ta.translation_key WHERE ta.area=:area AND t.language=:language",['area'=>$data['area'],'language'=>$language]);
    echo json_encode($rows);
?>