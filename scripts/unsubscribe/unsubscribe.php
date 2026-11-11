<?php namespace Metayota; include_once('../metayota/library.php');  
// Query String bereinigen - CRLF entfernen
$queryString = str_replace(["\r", "\n", "\0"], '', $_SERVER['QUERY_STRING']);

// Optional: Länge begrenzen
$queryString = substr($queryString, 0, 1000);

$redirectUrl = '/mail-settings#' . $queryString;
header("Location: $redirectUrl");
exit;
?>