<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');   
    


$resource = $db->get('resource',['visibility'=>'published','name'=>$_GET['resourcename']]);
$result = [];
if (!empty($resource)) {
    $translationKeys = getAllTranslationKeys($db,$resource['name']);
    $languages = explode(',',$_GET['languages']);
    
    foreach($languages as $language) {
        $result[$language] = [];
        foreach($translationKeys as $translationKey) {
            $translation = $db->get('translation',['language'=>$language,'translation_key'=>$translationKey]);
            if (!empty($translation)) {
                $result[$language][$translation['translation_key']] = $translation['translation'];
            }
        }
    }
}
echo json_encode($result);
?>