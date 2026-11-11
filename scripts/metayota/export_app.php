<?php
namespace Metayota;
include_once('../metayota/library.php');

if (getConfig('main_server')) {
    $server = $db->getById('server',$_SESSION['server']);
    $resources = $serverDB->getAll('resource');
    $httpHost = str_replace('https://','http://',$server['http_host']);
} else {
    $resources = $db->getAll('resource');
    $httpHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'].'/';
}

foreach($resources as $resource) {
    if (getConfig('wordpress') || $server['software'] == 'wordpress') {
        echo file_get_contents($httpHost.'wp-content/plugins/metayota/scripts/metayota/write_script.php?resourcename='.$resource['name']);//?==
    } else {
        echo file_get_contents($httpHost.'call/metayota/write_script?resourcename='.$resource['name']);   
    }
}
?>