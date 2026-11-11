<?php namespace Metayota; include_once('../metayota/library.php');  
  $user_id = $_SESSION['loggedInUser']['id'];
  echo json_encode($db->getAll('transactions', ['user_id' => $user_id]));
?>