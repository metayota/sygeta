<?php namespace Metayota; include_once('../metayota/library.php');   
    echo json_encode(['api_key'=>getConfig('google_maps_api_key')]); 
?>