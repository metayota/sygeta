<?php namespace Metayota; include_once('../metayota/library.php');    
    include('../mail-manager/lib.php');
    echo makeAPICall('delete_alias', $data);
?>