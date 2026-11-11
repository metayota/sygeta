<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');   
    include(__DIR__ . '/lib.php');
    $useDB = $db;
    if (getConfig('main_server')) {
        $useDB = $serverDB;
    }  

    $translations = $data['translations'];
    $translationKey = $data['translation_key'];

    if (!getConfig('main_server')) {
        checkPermissionsForTranslation($translationKey, $useDB);
    }

    foreach($translations as $translation) {
        $where = ['translation_key'=>$translationKey, 'language'=>$translation['language']];
        $existingTranslation = $useDB->get('translation',$where);
        if (empty($existingTranslation)) {
            $where['translation'] = $translation['translation'];
            $where['user_id'] = $user_id;
            $useDB->insert('translation',$where);
        } else {
            if (isset($translation['translation'])) {
                $useDB->update('translation',['translation'=>$translation['translation']],$where);
            }
        }

        if (!empty($translation['translation_category'])) {
            $useDB->insert('translation_area',['translation_key'=>$translationKey,'area'=>$translation['translation_category'], 'user_id'=>$user_id]);
        }
    }
    
  
    success();
?>