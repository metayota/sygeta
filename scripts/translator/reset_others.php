<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');   
    $sql = "UPDATE translation SET translation=NULL WHERE translation_key=:translation_key AND language!=:language";
    $serverDB->query($sql,['translation_key'=>$data['translation_key'],'language'=>$data['language']]);
    success();
?>