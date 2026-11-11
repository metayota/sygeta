<?php
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../translation-service/translation-service.php');
    
    $config = [];
    $config['user_options'] = [
        [
            'name'=>translate('create_new_user'), 
            'value'=> 'create_user'
        ], 
        [
            'name'=>translate('use_existing_user'), 
            'value'=> 'existing_user'
        ]
    ];
    jsonResponse($config);
?>