<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../chat-gpt/chat-gpt.php');   include_once('../deepl/deepl.php');   include_once('../translation-service/translation-service.php');   include_once('../ai-translator/ai-translator.php');  
include(__DIR__ . '/lib.php');
if ($data['close_connection']) {
    fastcgi_finish_request();
}

$useDB = $db;
if (getConfig('main_server')) {
    $useDB = $serverDB;
}  

$mainLanguage = getConfig('main_language');
// Assuming $data is the data received from your JavaScript
$existingTranslations = $data['existing_translations'];
$neededLanguages = $data['needed_languages'];
$translationKey = $data['translation_key'];

if (!getConfig('main_server')) {
    checkPermissionsForTranslation($translationKey);
}
// Construct a string that includes the existing translations
$translated = [];



// Save translations to database if $data['save'] is set
if (isset($data['save']) && $data['save']) {
    
    // First, save existing translations
    foreach ($existingTranslations as $existingTranslation) {
        if (!empty($existingTranslation['translation'])) {
            // Check if translation already exists in database
            $existingRow = $useDB->get('translation', array(
                'translation_key' => $translationKey,
                'language' => $existingTranslation['language']
            ));
            
            $translationData = array(
                'translation_key' => $translationKey,
                'translation' => $existingTranslation['translation'],
                'language' => $existingTranslation['language'],
                'user_id' => $user_id
            );
            
            if ($existingRow) {
                // Update existing translation
                $useDB->update('translation', $translationData, array(
                    'translation_key' => $translationKey,
                    'language' => $existingTranslation['language']
                ));
            } else {
                // Insert new translation
                $useDB->insert('translation', $translationData);
            }
        }
    }
}

if (!empty(getConfig('gemini_api_key'))) {
    if (!empty($neededLanguages)) {
        $result = translateWithAI($translationKey, $existingTranslations[0]['translation'], $existingTranslations[0]['language'], $neededLanguages);
        $translated = $result; // Assuming $result contains the translated array
    } else {
        $translated = [];
    }
} else {
    foreach ($neededLanguages as $neededLanguage) {
        
        if (!empty(getConfig('deepl_api_key'))) {
            foreach ($existingTranslations as $existingTranslation) {
                if (!empty($existingTranslation['translation'])) {
                    $translated[$neededLanguage] = translateDeepL( $existingTranslation['translation'], $existingTranslation['language'], $neededLanguage);
                    continue;
                }
            }
        } else {
            $chatGPT = new ChatGPT();
            $chatMessages = [];
            $existingTranslationsStr = "I have a word, or sentence, or text in ".count($existingTranslations)." languages. I want to translate it to ".translateLanguage($mainLanguage,'language_'.$neededLanguage).". Please just give me the translated text, no other comments. This is the translation key '$translationKey', it could give hints about the context. The following words are to translate:";
            
            $chatMessages[] = getMessageObject($existingTranslationsStr, 'assistant');
            foreach ($existingTranslations as $existingTranslation) {
                $gpttext =  translateLanguage($mainLanguage,'language_'.$existingTranslation['language']).": \"{$existingTranslation['translation']}\"";
                $chatMessages[] = getMessageObject($gpttext, 'assistant');
            }
            $chatMessages[] = getMessageObject("Translate it to ".translateLanguage($mainLanguage,'language_'.$neededLanguage).": ", 'assistant');
            
            
            // Get the translation from GPT-3
            $translatedText = $chatGPT->chat($chatMessages);
            $translatedText = trim($translatedText, "'\"");
            $translated[$neededLanguage] = $translatedText;
        }
    }
}


// Save translations to database if $data['save'] is set
if (isset($data['save']) && $data['save']) {
    
    
    // Then, save newly translated texts
    foreach ($translated as $language => $translation) {
        // Check if translation already exists
        $existingRow = $useDB->get('translation', array(
            'translation_key' => $translationKey,
            'language' => $language
        ));
        
        $translationData = array(
            'translation_key' => $translationKey,
            'translation' => $translation,
            'language' => $language,
            'user_id' => $user_id
        );
        
        if ($existingRow) {
            // Update existing translation
            $useDB->update('translation', $translationData, array(
                'translation_key' => $translationKey,
                'language' => $language
            ));
        } else {
            // Insert new translation
            $useDB->insert('translation', $translationData);
        }
    }
}

echo json_encode($translated);
?>