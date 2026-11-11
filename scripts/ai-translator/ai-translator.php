<?php namespace Metayota; include_once('../metayota/library.php');  

/**
 * Translate text using Gemini AI with predefined schema and context hints
 * 
 * @param string $inputText The text to translate
 * @param array $targetLanguages Array of language codes to translate to
 * @param string $model Gemini model to use (default: gemini-2.5-flash)
 * @return array Associative array with language codes as keys and translations as values
 * @throws Exception If translation fails
 */function translateWithAI($translationKey, $inputText, $inputLanguage, $targetLanguages, $model = "gemini-2.5-flash") {
    global $db;
    
    // For long text, process languages individually and merge results
    if (strlen($inputText) > 500 && count($targetLanguages) > 1) {
        $allTranslations = [];
        
        foreach ($targetLanguages as $language) {
            try {
                $singleTranslation = translateWithAI($translationKey, $inputText, $inputLanguage, [$language], $model);
                $allTranslations = array_merge($allTranslations, $singleTranslation);
                
                // Add small delay between individual calls to avoid rate limiting
                usleep(500000); // 0.5 second delay
            } catch (Exception $e) {
                // Log error but continue with other languages
                error_log("Translation failed for language $language: " . $e->getMessage());
                // Optionally add the language with empty translation or skip
                $allTranslations[$language] = $inputText; // Fallback to original text
            }
        }
        
        return $allTranslations;
    }
    
    // Original logic for shorter texts or single language
    if (getConfig('main_server')) {
        $translationHint = $db->getColumn('server','translation_hint',['id'=>$_SESSION['server']]);
    } else {
        $translationHint = getConfig('translation_hint');
    }

    if (str_contains($translationKey,'title')) {
        $translationHint.= " The text to be translated should use title capitalization / title form for languages where this is common.";
    }

    $apiKey = getConfig('gemini_api_key');

    $langRow = $db->get("language",['language_key'=>$inputLanguage]);
    if ($langRow) {
        $inputLanguage = $langRow['language_name']." ($inputLanguage)";
    }
    
    // Build the payload
    $payload = [
        'contents' => [
            [
                'parts' => [
                    [
                        'text' => $translationHint . "\nDo NOT translate {variable_names} (text in curly brackets). Set at the same places newlines like in the input text (use \\n in json). This is the translation_key, it could give a hint about the context: $translationKey\nThe input language is: $inputLanguage\n Target Languages:"
                        . implode(", ", $targetLanguages) . "\n\Translate only the following text into these languages, no additional comments: \"$inputText\""
                    ]
                ]
            ]
        ],
        'generationConfig' => [
            'response_mime_type' => 'application/json',
            'response_schema' => generateTranslationSchema($targetLanguages)
        ]
    ];

    // Make API call
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Handle errors
    if ($curlError) {
        throw new \Exception("Curl error: $curlError");
    }
    
    if ($httpCode !== 200) {
        throw new \Exception("API request failed with HTTP code $httpCode. Response: $response");
    }
    
    // Parse response
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new \Exception("API error: " . json_encode($result['error']));
    }
    
    // Extract translations
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new \Exception("Missing translation data in API response.");
    }
    
    $translationsJson = $result['candidates'][0]['content']['parts'][0]['text'];
    $translations = json_decode($translationsJson, true);
    
    if (!is_array($translations)) {
        throw new \Exception("Unexpected format in translations response: $translationsJson");
    }
    
    // Clean up translations (remove quotes)
    $cleanedTranslations = [];
    foreach ($translations as $langKey => $translatedText) {
        $cleanedTranslations[$langKey] = trim($translatedText, "\"'");
    }
    
    return $cleanedTranslations;
}

/**
 * Generate JSON schema for translation response
 * 
 * @param array $languages Array of language codes
 * @return array JSON schema structure
 */
function generateTranslationSchema($languages) {
    $properties = [];
    $required = [];
    
    foreach ($languages as $language) {
        $properties[$language] = [
            'type' => 'string'
        ];
        $required[] = $language;
    }
    
    return [
        'type' => 'object',
        'properties' => $properties,
        'required' => $required
    ];
}
?>