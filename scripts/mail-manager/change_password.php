<?php namespace Metayota; include_once('../metayota/library.php');    
    include('../mail-manager/lib.php');
    echo makeAPICall('change_password', $data);
?>