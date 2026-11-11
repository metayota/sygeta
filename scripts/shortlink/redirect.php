<?php namespace Metayota; include_once('../metayota/library.php');  
include('../shortlink/lib.php');

// Short-Code aus GET Parameter
$shortCode = $_GET['code'] ?? '';

// Short-Code validieren (8 Zeichen, nur a-z und 0-9)
if (!preg_match('/^[a-z0-9]{8}$/', $shortCode)) {
  http_response_code(404);
  die('Ungültiger Short-Code');
}

// Original URL abrufen
$originalUrl = getOriginalUrl($shortCode);

if (!$originalUrl) {
  http_response_code(404);
  die('Short-Code nicht gefunden');
}

// Klicks zählen
$db->query(
  "UPDATE shortlink SET clicks = clicks + 1 WHERE short_code = :code", 
  ['code' => $shortCode]
);

// Weiterleitung
header("Location: " . $originalUrl, true, 301);
exit;
?>