<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../translation-service/translation-service.php');
    
    $info = ['main_server'=>getConfig('main_server'),'edit_link'=>getConfig('database_table_editor_link')];
    echo json_encode($info);
?>