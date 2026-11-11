<?php
    include('../translation-service/translation-service.php');

   function generateCode($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }

    function insertTranslationWithRandomCode($baseTranslationKey, $translation, $lang=NULL) {
        global $language;
        if (!$lang) {
            $lang = $language;
        }
        $translationKey = $baseTranslationKey.'_'.generateCode();
        $db->insert('translation', ['translation_key'=>$translationKey, 'translation'=>$translation, 'language'=>$lang]);
        return $translationKey;
    } 
?>