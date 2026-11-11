<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  

// WARINING IT'S THE SAME CODE LIKE DB.GET EXCEPT FETCHALL.. (and where id='')

function parseConditions($input) {
    if (!is_string($input)) {
        return $input;
    }
    
    $result = [];
    $conditions = explode('and', $input);
    
    foreach ($conditions as $condition) {
        $parts = explode('=', trim($condition));
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $result[$key] = is_numeric($value) ? $value + 0 : $value;
        }
    }
    
    return $result;
}


global $db;
$useDB = $db;
if (empty($data)) {
    $data = $get;
}
if (isset($data['where'])) {
    $data['where'] = parseConditions($data['where']);
}
if (!empty($data['serverdb']) && !empty($serverDB)) {
    $useDB = $serverDB;
}
/*if (!empty($serverDB)) {
    $useDB = $serverDB;
    $db = $serverDB;
}*/
/*$resource = $db->fetch('resource',array('name' => $data['table']));
if (!empty($resource) &&!empty($resource['configuration'])) {
    $config = json_decode($resource['configuration']);
    if ($config->clientdb == true) {
        $useDB = $serverDB;
    }
}*/
function getFromDBTranslated($useDB, $translatedColumns, $table, $values) {
    
    // Map values to include table prefix
    $mappedValues = [];
    foreach ($values as $key => $value) {
        $mappedValues[$table . '.' . $key] = $value;
    }
    
    $whereStatement = $useDB->getWhereStatement($mappedValues);
    $tableEscaped = $useDB->quoteIdentifier($table);
    
    // Constructing the JOIN statements and selecting translated columns with aliases
    $joinStatements = "";
    $selectTranslatedColumns = "";
    foreach ($translatedColumns as $column) {
        $columnEscaped = $useDB->quoteIdentifier($column);
        $joinStatements .= " LEFT JOIN translation AS {$column}_translation ON $tableEscaped.$columnEscaped = {$column}_translation.translation_key AND {$column}_translation.language = :language ";
        $selectTranslatedColumns .= ", COALESCE({$column}_translation.translation, $tableEscaped.{$column}) AS {$column}_translated";
    }
    
    // Building the final query
    $mappedValues['language'] = $GLOBALS['language'];
    $query = "SELECT $tableEscaped.* $selectTranslatedColumns FROM $tableEscaped $joinStatements $whereStatement";
    return $useDB->getAllByQuery($query, $mappedValues);
}


if (!empty($serverDB) || hasAccess('db.table.get_all',$data['table'])) { // not on the same server (like $serverDB)
$where = isset($data['where']) ? $data['where'] : [];
} else if (hasAccess('db.table.list.owned',$data['table'])) {
    $where = isset($data['where']) ? $data['where'] : [];
    $where['user_id'] = $user_id;
} else {
    
    http_response_code(403);
    echo json_encode(['message'=>'You have no access to the database table '.$data['table'],'highlight'=>'db-list-data','resource'=>$data['table']]);
    die();
}
if (!empty($data['translate_columns'])) {
    $result = getFromDBTranslated($useDB, $data['translate_columns'],$data['table'],$where);
} else {
    $result = $useDB->fetchAll($data['table'],$where);
}
echo json_encode($result);
/*

<?php
$useDB = $db;

if (empty($data)) {
    $data = $get;
}

if (!empty($serverDB) && !empty($data['serverdb'])) {
    $useDB = $serverDB;
}


$cachetime = $db->getColumnByName('resource','cachetime',$data['table']);
if (!empty($cachetime)) {
    //   sendCacheHeaders($cachetime);
}


$attributes = array();
if (isset($data['fields'])) {
    $attributes['fields'] = $data['fields'];
}
if (!empty($serverDB) || hasAccess('db.table.get_all',$data['table'])) {
    $where = isset($data['where']) ? $data['where'] : null;
    
    echo json_encode($useDB->fetchAll($data['table'],$where, $attributes));
} else if (hasAccess('db.table.list.owned',$data['table'])) {
    $where = isset($data['where']) ? $data['where'] : null;
    $where['user_id'] = $loggedInUser['id'];
    
    echo json_encode($useDB->fetchAll($data['table'],$where, $attributes));
} else {
    errorMsg('No permission');
}
?>*/
?>