<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');   
    $info = [];
    $info['main_language'] = getConfig('main_language');
    $info['current_language'] = $GLOBALS['language'];
    $info['auto_translate'] = !empty(getConfig('deepl_api_key'));
    $languages = $db->getAll('language');
    foreach($languages as &$language) {
        $language['language_translated']= translate('language_'.$language['language_key']);
    }
    $info['languages'] = $languages;
    echo json_encode($info);
?>