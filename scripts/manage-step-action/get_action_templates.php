<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $sql = 'SELECT sat.name as value, t.translation as name, sat.form_resource 
            FROM step_action_template sat
            LEFT JOIN translation t
            ON sat.title = t.translation_key 
            AND t.language=:language';
    $stepActionTemplates = $db->getAllByQuery($sql, ['language'=>$GLOBALS['language']]);
    echo json_encode($stepActionTemplates);
?>