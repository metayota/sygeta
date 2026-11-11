<?php namespace Metayota; include_once('../metayota/library.php');  
  $news = $db->getAll('news');
  echo json_encode($news);
?>