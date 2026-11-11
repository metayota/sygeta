<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $useDB = !empty($serverDB) ? $serverDB : $db;
    if (!empty($useDB)) {
        $result = $useDB->getAllByQuery("SELECT r.name, r.title, t.translation as title_translated, r.documentation, r.type, r.project_id, r.extends_resource FROM resource r LEFT JOIN translation t ON t.translation_key = r.title and t.language=:language ORDER BY title_translated",['language'=>$GLOBALS['language']]);
        echo json_encode($result);
    } else {
        echo '[]';
    }
?>