<?php namespace Metayota; include_once('../metayota/library.php');   
    $info = [];
    $info['main_language'] = getConfig('main_language');
    $info['current_language'] = $GLOBALS['language'];
    $info['auto_translate'] = !empty(getConfig('deepl_api_key'));
    $info['all_languages'] = $db->getAll('language');
    echo json_encode($info);
?>