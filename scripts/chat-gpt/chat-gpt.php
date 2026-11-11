<?php namespace Metayota; include_once('../metayota/library.php');  
function getMessageObject($message,$role) {
    return array(
        'role' => $role,
        'content' => $message
    );
}

class ChatGPT {
    private $api_key;
    private $engine;

    function __construct($engine='gpt-4-1106-preview') { //gpt-3.5-turbo
        $this->api_key = getConfig('chatgpt_api_key');
        $this->engine = $engine;
    }

    function generate_text($messages, $temperature=0.5, $max_tokens=50) {
        usleep(30000);
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key,
        ];
        $data = [
            'model' => 'gpt-4-1106-preview',
            'messages' => $messages,
            'max_tokens' => $max_tokens,
            'temperature' => $temperature,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        var_dump($response);
        $result = json_decode($response, true);
        
        if (isset($result['code']) && $result['code'] == 'rate_limit_exceeded') {
            return null;
        }
        $answer = str_replace("\r\n","\n",$result['choices'][0]['message']['content']);
        return trim($answer);
    }

    function chat($messages, $temperature=0.5, $max_tokens=1500) {
        $response = '';
        return $this->generate_text($messages, $temperature, $max_tokens);
    }
}


?>