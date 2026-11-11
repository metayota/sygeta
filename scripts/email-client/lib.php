<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  

function decodeEmailSubject($subject) {
    // MIME-kodierte Betreffzeilen dekodieren
    $subjectParts = imap_mime_header_decode($subject);
    $decodedSubject = '';

    foreach ($subjectParts as $element) {
        $charset = strtoupper($element->charset);
        $text = $element->text;

        if ($charset === 'DEFAULT') {
            $charset = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'ISO-8859-15', 'WINDOWS-1252'], true) ?: 'UTF-8';
        }

        // Text immer nach UTF-8 konvertieren, falls nicht bereits UTF-8
        if ($charset !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $charset);
        }

        // Fehlerhafte doppelte UTF-8-Kodierungen korrigieren
        if (preg_match('/Ã¤|Ã¶|Ã¼|Ã„|Ã–|Ãœ|ÃŸ/', $text)) {
            $text = utf8_decode($text);
        }

        $decodedSubject .= $text;
    }

    // Unicode-Normalisierung (falls möglich)
    if (class_exists('Normalizer')) {
        $decodedSubject = normalizer_normalize($decodedSubject, \Normalizer::FORM_C);
    }

    return trim($decodedSubject);
}

?>