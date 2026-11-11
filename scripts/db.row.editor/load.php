<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
$table = $data['table'];
$useDB = $db;

// Check for main server configuration
if (getConfig('main_server')) {
    $useDB = $serverDB;
    $allAccess = true;
} else {
    $allAccess = false;
}

// Check access rights
if (!$allAccess && !hasAccess('db.table.get_all',$table) && !hasAccess('database',$table)) {
    if (hasAccess('db.table.list.owned',$table)) {
        // User can only access their own rows
        $userRestriction = true;
    } else {
        http_response_code(403);
        echo json_encode(['message'=>'You have no access to the database table '.$table,'highlight'=>'db','resource'=>$table]);
        die();
    }
} else {
    $userRestriction = false;
}

// Get the row based on name or id
if (!empty($data['row_name'])) {
    if ($userRestriction) {
        $result = $useDB->get($table, array('user_id'=>$user_id, 'name'=>$data['row_name']));
    } else {
        $result = $useDB->getByName($table, $data['row_name']);
    }
} else {
    if ($userRestriction) {
        $result = $useDB->get($table, array('user_id'=>$user_id, 'id'=>$data['rowid']));
    } else {
        $result = $useDB->getById($table, $data['rowid']);
    }
}

// If row found and resourcetype is specified, load translations
if ($result && !empty($data['resourcetype'])) {
    $resource = $useDB->getByName('resource', $data['resourcetype']);
    if ($resource) {
        $parameters = json_decode($resource['parameters']);
        
        foreach($parameters as $parameter) {
            // Handle translated fields
            if ($parameter->type == 'translated_string' || $parameter->type == 'translated_formatted_string') {
                $fieldName = $parameter->name;
                $translationKey = $result[$fieldName];
                
                if ($translationKey) {
                    // Fetch all translations for this key
                    $translations = $useDB->getAllByQuery(
                        "SELECT language, translation FROM translation WHERE translation_key = :key",
                        ['key' => $translationKey]
                    );
                    
                    // Build simple translations object
                    $translationsArray = [];
                    foreach($translations as $trans) {
                        $translationsArray[$trans['language']] = $trans['translation'];
                    }
                    
                    // Replace field with simple structure
                    $result[$fieldName] = [
                        'value' => $translationKey,
                        'translations' => $translationsArray
                    ];
                }
            }
            
            // Handle db_row fields (referenced tables)
           /* if ($parameter->type == 'db_row' && !empty($result[$parameter->name])) {
                $dbtable = $parameter->options->dbtable;
                $titlefield = $parameter->options->titlefield;
                $idfield = $parameter->options->idfield;
                
                // Optionally fetch the referenced row's title
                $referencedRow = $useDB->getByQuery(
                    "SELECT $titlefield FROM $dbtable WHERE $idfield = :id",
                    ['id' => $result[$parameter->name]]
                );
                
                if ($referencedRow) {
                    // Add the title as an additional field
                    $result[$parameter->name . '_title'] = $referencedRow[$titlefield];
                }
            }*/
        }
    }
}

echo json_encode($result);
?>