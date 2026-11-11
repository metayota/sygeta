<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');  
$useDB = $db;
if (getConfig('main_server')) {
    $useDB = $serverDB;
    $db = $serverDB;
}

if (empty($data['translation_key']) && !empty($data['translation'])) {
    $result = $useDB->getByQuery("SELECT t.* FROM translation_area ta INNER JOIN translation t ON t.translation_key = ta.translation_key WHERE (t.translation = :translation) AND ta.area=:area AND t.language=:language",['translation'=>$data['translation'],'area'=>$data['translation_category'],'language'=>$language]);
    if (!empty($result['translation_key'])) {
        $data['translation_key'] = $result['translation_key'];
    }
}

if (!empty($data['including_main_language']) && $data['including_main_language'] == true) {
    $languages = $useDB->getAll('language');
} else {
    $languages = $useDB->getAllByQuery('SELECT * FROM language WHERE language_key != :language', ['language'=>$GLOBALS['language']]);
}

if (empty($data['translation_key'])) {
    $translation = [];
    $translation['translation_key'] = '';
    $translation['translation'] = '';
    $translation['language'] = $GLOBALS['language'];
    $translation['not_found'] = true;
    
    $translation['other_languages'] = [];
    foreach($languages as $language) {
        $otherLanguage = [];
        $otherLanguage['language_translated'] = translate('language_'.$language['language_key']);
        $otherLanguage['translation'] = '';
        $otherLanguage['language'] = $language['language_key'];
        $otherLanguage['translation_key'] = '';
        $translation['other_languages'][] = $otherLanguage;
    }
} else {
    $translation = $useDB->get('translation',['translation_key'=>$data['translation_key'],'language'=>$GLOBALS['language']]);
    
    if (empty($translation)) {
        $translation = [];
        $translation['translation_key'] = $data['translation_key'];
        $translation['translation'] = '';
        $translation['language'] = $GLOBALS['language'];
        $translation['not_found'] = true;
    }
    
    $translation['other_languages'] = [];
    foreach($languages as $language) {
        $otherLanguage = $useDB->getByQuery('SELECT * FROM translation WHERE translation_key=:translation_key AND language=:language',['translation_key'=>$data['translation_key'],'language'=>$language['language_key']]);
        if (!empty($otherLanguage)) {
            
            $otherLanguage['language_translated'] = softTranslate('language_'.$otherLanguage['language']);
            $otherLanguage['translation'] = empty($otherLanguage['translation']) ? '' : $otherLanguage['translation'];
        } else {
            $otherLanguage = [];
            $otherLanguage['language_translated'] = translate('language_'.$language['language_key']);
            $otherLanguage['translation'] = '';
            $otherLanguage['language'] = $language['language_key'];
            $otherLanguage['translation_key'] = $translation['translation_key'];
        }
        
        $translation['other_languages'][] = $otherLanguage;
    }
}

echo json_encode($translation);
?>