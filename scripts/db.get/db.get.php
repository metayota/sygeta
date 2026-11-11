<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    global $db;
    $useDB = $db;
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
        $whereStatement = $useDB->getWhereStatement($values);
        $tableEscaped = $useDB->quoteIdentifier($table);

        // Constructing the JOIN statements and selecting translated columns with aliases
        $joinStatements = "";
        $selectTranslatedColumns = "";
        foreach ($translatedColumns as $column) {
            $columnEscaped = $useDB->quoteIdentifier($column);
            $joinStatements .= " LEFT JOIN translation AS {$column}_translation ON $tableEscaped.$columnEscaped = {$column}_translation.translation_key AND {$column}_translation.language=:language ";
            $selectTranslatedColumns .= ", {$column}_translation.translation AS {$column}_translated";
        }

        // Building the final query
        $query = "SELECT $tableEscaped.* $selectTranslatedColumns FROM $tableEscaped $joinStatements $whereStatement";
        $values['language'] = $GLOBALS['language'];

        return $useDB->getByQuery($query,$values);
    }
    
    
    
    if (!empty($serverDB) || hasAccess('db.table.get_all',$data['table'])) { // not on the same server (like $serverDB)

        $where = isset($data['where']) ? $data['where'] : ['id'=>$data['id']];
    } else if (hasAccess('db.table.list.owned',$data['table'])) {
        $where = isset($data['where']) ? $data['where'] : array('id'=>$data['id']);
        $where['user_id'] = $user_id;
    } else {
        http_response_code(403);
        echo json_encode(['message'=>'You have no access to the database table '.$data['table'],'highlight'=>'db-list-data','resource'=>$data['table']]);
        die();
    }

    if (!empty($data['translate_columns'])) {
        $newWhere = [];
        foreach ($where as $key => $value) {
            $newWhere[$data['table'] . '.' . $key] = $value;
        }
        $where = $newWhere;
        $result = getFromDBTranslated($useDB, $data['translate_columns'],$data['table'],$where);
    } else {
        $result = $useDB->get($data['table'],$where);
    }
    echo json_encode($result);
?>