<?php 
namespace Metayota; //update 1
include_once('../metayota/library.php');   
include_once('../sync-functions/sync-functions.php');   
include_once('../translation-service/translation-service.php');   

if (empty($serverDB)) {
    die();
}

$resourcename = $data['resourcename'];
$repository_id = $data['repository'];
$repository = $serverDB->getById('repository', $repository_id);
$repositoryURL = $repository['url'];

$server = $db->getById('server', $_SESSION['server']);

// Get remote resource (source)
$remoteUrl = "$repositoryURL/call/rc.sync/get_resource?resourcename=" . $resourcename . '&api_key=' . urlencode($repository['api_key']);
$remoteResourceJson = (array) json_decode(file_get_contents($remoteUrl));
unset($remoteResourceJson['id']);

// Get local resource (target)
if (empty($server['sync_api_key'])) {
    errorMsg('You must have a metayota_sync_api_key in config and in server / Please contact the support.');
}
$localUrl = $server['http_host'] . "call/rc.sync/get_resource?resourcename=" . $resourcename . '&api_key=' . urlencode($server['sync_api_key']);
$localResourceJson = @file_get_contents($localUrl);
$localResource = $localResourceJson ? (array) json_decode($localResourceJson) : null;

// Get remote implementation (source files)
$remoteImplementation = json_decode($remoteResourceJson['implementation'], true);
$remoteFiles = !empty($remoteImplementation) ? array_keys($remoteImplementation) : [];
unset($remoteResourceJson['implementation']);

// Get local implementation (target files)
$localImplementation = !empty($localResource) ? json_decode($localResource['implementation'], true) : [];
$localFiles = !empty($localImplementation) ? array_keys($localImplementation) : [];

// Delete files that only exist on target server
$filesToDelete = array_diff($localFiles, $remoteFiles);

$writeSuccess = true;
$writeScriptResult = '';

if (!empty($filesToDelete)) {
    foreach ($filesToDelete as $fileToDelete) {
        $postData = [
            'resourcename' => $resourcename,
            'file' => $fileToDelete
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $server['sync_api_key'],
                    'Referer: https://metayota.com/'
                ],
                'content' => json_encode($postData)
            ]
        ];

        $context = stream_context_create($options);

        if (getConfig('wordpress')) {
            $url = $server['http_host'] . 'wp-content/plugins/metayota/scripts/metayota/delete_file.php';
        } else {
            if ($server['software'] == 'wordpress') {
                $url = $server['http_host'] . 'wp-content/plugins/metayota/scripts/metayota/delete_file.php';
            } else {
                $url = $server['http_host'] . 'call/metayota/delete_file';
            }
        }
        
        $deleteResult = file_get_contents($url, false, $context);
    }
}

$oldResource = $serverDB->getByName('resource', $resourcename);

// Update database
$serverDB->delete('resource', ['name' => $resourcename]);
$serverDB->insert('resource', $remoteResourceJson);

$newResource = $serverDB->getByName('resource', $resourcename);

$dbUpdateRequired = ($newResource['type'] == 'dbtable' && !empty($oldResource) && $oldResource['parameters'] != $newResource['parameters']);
$dbInstallRequired = ($newResource['type'] == 'dbtable' && empty($oldResource));

// Write all files from remote resource using new write_script approach
if (!empty($remoteImplementation)) {
    foreach ($remoteImplementation as $filename => $fileContent) {
        $postData = [
            'resourcename' => $newResource['name'],
            'file' => $filename,
            'content' => $fileContent,
            'resource_type' => $newResource['type'],
            'dependencies' => $newResource['dependencies']
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $server['sync_api_key'],
                    'Referer: https://metayota.com/'
                ],
                'content' => json_encode($postData)
            ]
        ];

        $context = stream_context_create($options);

        if (getConfig('wordpress')) {
            $url = $server['http_host'] . 'wp-content/plugins/metayota/scripts/metayota/write_script.php';
        } else {
            if ($server['software'] == 'wordpress') {
                $url = $server['http_host'] . 'wp-content/plugins/metayota/scripts/metayota/write_script.php';
            } else {
                $url = $server['http_host'] . 'call/metayota/write_script';
            }
        }

        $fileWriteResult = file_get_contents($url, false, $context);
        
        if (!empty($fileWriteResult)) {
            $fileWriteResultArr = json_decode($fileWriteResult, true);
            if ($fileWriteResultArr['success'] !== true) {
                $writeSuccess = false;
                $writeScriptResult = $fileWriteResult;
                break;
            }
        }
    }
}

// Run setup if exists
if (isset($remoteImplementation['setup.php'])) {
    $accessData = ['access_right' => 'call', 'resource_name' => $resourcename, 'usergroup_id' => 1];
    $accessRow = $serverDB->get('access', $accessData);
    $deleteAccess = false;
    if (empty($accessRow)) {
        $deleteAccess = true;
    }
    if (getConfig('wordpress')) {
        include('../../scripts/' . $resourcename . '/setup.php');
    } else {
        file_get_contents($server['http_host'] . 'call/' . $resourcename . '/setup');
    }
    if (empty($accessRow) && $deleteAccess) {
        $serverDB->delete('access', $accessData);
    }
}

// Sync translations
syncTranslations($repositoryURL, $serverDB, $resourcename);

$result = [
    'success' => $writeSuccess,
    'type' => $writeSuccess ? 'success' : 'error',
    'message' => $writeSuccess ? translate('sync_single_file_success') : translate('sync_single_file_failed'),
    'db_update_required' => $dbUpdateRequired,
    'db_install_required' => $dbInstallRequired
];

if ($writeSuccess !== true) {
    $result['native_message'] = $writeScriptResult;
}

echo json_encode($result);
?>
