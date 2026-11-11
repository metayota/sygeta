<?php namespace Metayota; include_once('../metayota/library.php');  
  $properties = $db->getAll('property_listing');
  echo json_encode($properties);
?>