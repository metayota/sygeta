<?php namespace Metayota; include_once('../metayota/library.php');  
function askGeminiAI($prompt, $options = []) {
    try {
        $apiKey = getConfig('gemini_api_key');
        if (empty($apiKey)) {
            throw new \Exception('Gemini API key not found in configuration');
        }

        $baseUrl = 'https://generativelanguage.googleapis.com/v1';
        $model = 'gemini-1.5-flash-002';
        $endpoint = "{$baseUrl}/models/{$model}:generateContent";

        // Default options for the generation config
        $generationConfig = array_merge([
            'temperature' => 0.9,
            'top_p' => 1,
            'top_k' => 1,
            'max_output_tokens' => 2048,
            'responseMimeType' => 'application/json',  // Set mime type to application/json
        ], $options);

        // Add responseSchema to the configuration if provided in the options
        if (isset($options['responseSchema'])) {
            $generationConfig['responseSchema'] = $options['responseSchema'];
        }

        // Prepare the request data
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generation_config' => $generationConfig
        ];

        $ch = curl_init();
        $url = $endpoint . '?key=' . $apiKey;

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }

        $result = json_decode($response, true);

        // Default output format is 'text', extract text if not defined or set to 'text'
        $outputFormat = $options['outputFormat'] ?? 'text'; // Defaults to 'text'

        // Handle the output based on the format requested
        if ($outputFormat === 'json') {
            // Return the full response as JSON
            return json_encode($result, JSON_PRETTY_PRINT);
        } elseif ($outputFormat === 'text') {
            // Extract just the text from the response and return it
            return $result['candidates'][0]['content']['parts'][0]['text'];
        } else {
            // If an invalid format is provided, return an error message
            return 'Error: Invalid output format specified';
        }

    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}
?>