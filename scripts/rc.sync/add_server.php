<?php 
namespace Metayota;
include_once('../metayota/library.php');
include_once('../sync-functions/sync-functions.php');
include_once('../translation-service/translation-service.php');
requireAccess('database','repository');
$serverDB->insert('repository',$data);
success();
?>