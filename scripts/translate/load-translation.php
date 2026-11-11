<?php namespace Metayota; include_once('../metayota/library.php');   
    sendCacheHeaders(); 
    $clientTranslationAreas = getConfig('client_translation_areas');
    if (empty($clientTranslationAreas)) {
        $clientTranslationAreas = ['client'];
    }
    $inList = "'" . implode("','", $clientTranslationAreas) . "'";

    $whereArea = "INNER JOIN translation_area ON translation.translation_key = translation_area.translation_key AND (area in ($inList))";
    if (isset($_SESSION['loggedInUser']) && $_SESSION['loggedInUser']['usergroup_id'] == 3) {
        $whereArea = '';
    } 
    $language = isset($_GET['language']) ? $_GET['language'] : 'en';
    $translations = $db->getAllByQuery("SELECT translation.translation_key, translation,depends_on FROM translation $whereArea WHERE language = :language", ['language'=>$language]);
    $translationArr = [];
    foreach($translations as $translation) {
        if (empty($translation['depends_on'])) {
            $translationArr[$translation['translation_key']] = $translation['translation'];
        } else {
            $translationArr[$translation['translation_key']] = array('translation'=>$translation['translation'], 'depends_on'=>json_decode($translation['depends_on']));
        }
    }
    echo json_encode($translationArr);
?>