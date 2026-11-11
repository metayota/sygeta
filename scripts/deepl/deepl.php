<?php namespace Metayota; include_once('../metayota/library.php');  
function translateDeepL($text, $sourceLanguage, $targetLanguage) {
    $apiKey = getConfig('deepl_api_key'); 

    $ch = curl_init();


    // Pattern to match [[...]], [...] and {...} and replace them with <x></x> tags
$patterns = [
    '/\[\[([a-z0-9_]+)\]\](,?\s+)?/i',
    '/\[([a-z0-9_]+)\](,?\s+)?(?!])/i',
    '/\{([a-z0-9_]+)\}(,?\s+)?/i',
    '/(__[A-Z0-9_]+__)/',
    '/([\*]+)/' 
];

$replacements = [
    '<x>[[$1]]$2</x>',
    '<x>[$1]$2</x>',
    '<x>{$1}$2</x>',
    '<x>__$1__</x>',
    '<x>$1</x>'
];


    // Perform the replacement
    $text = preg_replace($patterns, $replacements, $text);


    $postData = http_build_query([
        'text' => $text,
        'tag_handling' => 'xml',
        'outline_detection' => 0,
        'non_splitting_tags' => 'x',
        'ignore_tags' => 'x',
        'source_lang' => $sourceLanguage,
        'target_lang' => $targetLanguage
    ]);
    if (getConfig('deepl_pro')) {
        curl_setopt($ch, CURLOPT_URL, 'https://api.deepl.com/v2/translate');
    } else {
        curl_setopt($ch, CURLOPT_URL, 'https://api-free.deepl.com/v2/translate');
    }
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: DeepL-Auth-Key {$apiKey}"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);
    
    
    if ($response) {
        $responseArray = json_decode($response, true);
        if (isset($responseArray['translations'][0]['text'])) {
            $translatedText = $responseArray['translations'][0]['text'];
            // Strings to be searched for
            $search = ['<x>', '</x>'];

            // Replace the strings with an empty string
            return str_replace($search, '', $translatedText);
        }
    }

    return null; 
}
?>