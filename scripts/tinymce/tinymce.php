<?php namespace Metayota; include_once('../metayota/library.php');  

header('Content-Type: application/javascript');

// Pfad zum Emoji-Bilderordner
$emojiFolderPath = '../../images/emojis-webp/';

// Dateien aus dem Ordner lesen
$files = scandir($emojiFolderPath);

// Ein Array für die TinyMCE Emoji-Datenbank aufbauen
$tinymceEmojis = [];

foreach ($files as $file) {
    // Überprüfen, ob die Datei eine PNG-Datei ist
    if (strpos($file, '.png') !== false && 
        !preg_match('/_1f3f[bdcef]\\.png$/', $file)) {
        $imagePath = '/call/image.resize?w=30&h=30&format=webp&src=images/emojis-webp/' . $file;
        $name = pathinfo($file, PATHINFO_FILENAME);  // Dateinamen ohne Erweiterung als Name verwenden

        // Da wir direkt aus dem Ordner lesen, ist keine Existenzprüfung erforderlich
        $tinymceEmojis[] = [
            'title' => $name,
            'src' => $imagePath,
            'alt' => $name
        ];
    }
    if (strpos($file, '.webp') !== false) {
        $imagePath = '/images/emojis-webp/' . $file;
        $name = pathinfo($file, PATHINFO_FILENAME);  // Dateinamen ohne Erweiterung als Name verwenden

        // Da wir direkt aus dem Ordner lesen, ist keine Existenzprüfung erforderlich
        $tinymceEmojis[] = [
            'title' => $name,
            'src' => $imagePath,
            'alt' => $name
        ];
    }
    if (strpos($file, '.svg') !== false && 
        !preg_match('/_1f3f[bdcef]\\.svg$/', $file)) {
        $imagePath = '/images/emojis-webp/' . $file;
        $name = pathinfo($file, PATHINFO_FILENAME);  // Dateinamen ohne Erweiterung als Name verwenden

        // Da wir direkt aus dem Ordner lesen, ist keine Existenzprüfung erforderlich
        $tinymceEmojis[] = [
            'title' => $name,
            'src' => $imagePath,
            'alt' => $name
        ];
    }
}

// Die Daten für TinyMCE ausgeben
echo json_encode($tinymceEmojis);


/*
header('Content-Type: application/javascript');
// Pfad zur emoji.json Datei aus dem emoji-data Projekt
$emojiDataPath = '../../images/emoji.json';

// Die Daten aus der Datei laden
$emojis = json_decode(file_get_contents($emojiDataPath), true);

// Ein Array für die TinyMCE Emoji-Datenbank aufbauen
$tinymceEmojis = [];

foreach ($emojis as $emoji) {
    $imagePath = '/images/72/' . $emoji['unified'].'.png';//openmoji-72x72-color
    $name = $emoji['name'];

    // Prüfen, ob die Bilddatei tatsächlich existiert
    if (file_exists('../..'.$imagePath)) {
        $tinymceEmojis[] = [
            'title' => $name,
            'src' => $imagePath,
            'alt' => $name
        ];
    }
}

// Die Daten für TinyMCE ausgeben
echo json_encode($tinymceEmojis);*/
?>