<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $sql = 'SELECT stt.*, t.translation as title_translated 
            FROM step_type_template stt 
            LEFT JOIN translation t 
            ON stt.title = t.translation_key AND t.language=:language';
    $stepTypeTemplates = $db->getAllByQuery($sql,['language'=>$GLOBALS['language']]);
    echo json_encode($stepTypeTemplates);
?>