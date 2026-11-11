<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');  
    if (empty($serverDB)) {
        die();
    }

    $repository_id = $data['repository'];
    $repository = $serverDB->getById('repository',$repository_id);
    $repositoryURL = $repository['url'];

  
    /// m
    if (!$serverDB) {
        $serverDB = $db;
    }

    if (!empty($repository['project_name'])) {
        $url = "$repositoryURL"."call/rc.resource.depending?project_name=".$repository['project_name'];
    } else if (!empty($repository['task_id'])) {
        $task = $db->getById('task',$repository['task_id']);
        $url = "$repositoryURL"."call/rc.sync/task_get_resources?resource=".$task['resource'];
    } else {
        $url = "$repositoryURL"."call/rc.sync/get_resources?api_key=".urlencode($repository['api_key']);
    }
   /* var_dump($repository_id);
    var_dump($repository);
    var_dump($url);*/
    $txt = file_get_contents($url);
    
    $foundResources = array();
    //header('Content-Type: application/json');
    $remoteResources = json_decode($txt);
    if (!$remoteResources) {
        echo '<h1>Could not parse URL</h1>';
        echo $txt;
        echo $url;
        die();
    }

    foreach($remoteResources as $remoteResource) {
        $resource = $serverDB->getByQuery('SELECT name, title, hash, version, version_txt, last_update, visibility FROM resource WHERE name=:name',array('name'=>$remoteResource->name));//public="1" AND

        if (empty($resource) || $resource['visibility'] != 'private') {
            if ($resource) {
                if ($remoteResource->hash == $resource['hash']) {
             //       $foundResources[] = array('name'=>$remoteResource->name,'remote_version'=>$remoteResource->version,'version'=>$resource['version'],'status'=>'identical','last_update'=>$resource['last_update'], 'remote_last_update' => $remoteResource->last_update);
                } else {
                    $foundResources[] = array('name'=>$remoteResource->name,'remote_version'=>$remoteResource->version,'version'=>$resource['version'],'status'=>'different','last_update'=>$resource['last_update'], 'remote_last_update' => $remoteResource->last_update);
                }
            } else {
                $foundResources[] = array('name'=>$remoteResource->name,'remote_version'=>$remoteResource->version,'version'=>0,'status'=>'new','last_update'=>$resource['last_update'], 'remote_last_update' => $remoteResource->last_update);   
            }
            if (isset($data['dosync'])) {
                $url = "$repositoryURL/call/rc.sync/get_resource?resourcename=".$remoteResource->name.'&api_key='.urlencode($repository['api_key']); //make sure api key is set, maybe search for better solution for tasks;
                $remoteResourceJson = (array) json_decode(file_get_contents($url));
                unset($remoteResourceJson['id']);
                //echo 'inserted resource '.$remoteResourceJson['name'].'<br/>';
                $serverDB->delete('resource',['name'=>$remoteResourceJson['name']]);
                $serverDB->insert('resource',$remoteResourceJson);

                syncTranslations($repositoryURL, $serverDB, $remoteResource->name);

                $impl = json_decode($remoteResourceJson['implementation'],true);
                if (!empty($impl['install.php'])) {
                    callAction($remoteResourceJson['name'],'install',['old_version'=>$resource['version'],'new_version'=>$remoteResourceJson['version']]);
                }
                if (!empty($impl['install.sql'])) {
                    $serverDB->query($impl['install.sql']);
                }

                success();

            }
        }
    }
    if (isset($data['dosync'])) {
        callAction('metayota','export_app');
    }
    echo(json_encode($foundResources));
?>