<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  


    $answer= askGeminiAI("I need an authentice email answer for the following email, in a simple HTML format for emails. Only the answer text is required, no subject again. Please think, you are the one in the to field, and the other one is the one in the from field!:\n\n".json_encode($data['email']));

    $answer =trim($answer,'```html');
    $answer =trim($answer,'```');
    $answer = str_replace('```','',$answer);
    
    $answer = trim ($answer);
    echo json_encode(['ai_answer'=>$answer]);
?>