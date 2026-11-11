<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');   
    $useDB = $db;
    if (getConfig('main_server')) {
        $useDB = $serverDB;
    }  
    $language = isset($data['language']) ? $data['language'] : $GLOBALS['language'];
    
    $where = ['translation_key'=>$data['translation_key'], 'language'=>$language];
    $existingTranslation = $useDB->get('translation',$where);
    if (empty($existingTranslation)) {
        $where['translation'] = $data['translation'];
        $useDB->insert('translation',$where);
    } else {
        if (isset($data['translation'])) {
            $useDB->update('translation',['translation'=>$data['translation']],$where);
        }
    }

    if (!empty($data['translation_category'])) {
        $useDB->insert('translation_area',['translation_key'=>$data['translation_key'],'area'=>$data['translation_category']]);
    }
    success();
?>