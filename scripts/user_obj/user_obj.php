<?php namespace Metayota; include_once('../metayota/library.php');  
    if (!empty($_SESSION['loggedInUser'])) {
        echo json_encode($_SESSION['loggedInUser']);
    } else {
        echo "{}";
    }
?>