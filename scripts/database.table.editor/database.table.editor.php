<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  
    $useDB = $db;
    if (getConfig('main_server')) {
        $useDB = $serverDB;
        $allAccess = true;
    } else {
        $allAccess = false;
    }

    $page = empty($data['page']) ? 0 : $data['page'] - 1;
    $table = $data['table'];
    $count = !empty($data['count']);
    $search = !empty($data['search']) ? $data['search'] : null;
    $where = !empty($data['where']) ? $data['where'] : null;

    // Check access rights
    if (!$allAccess && !hasAccess('db.table.get_all',$table) && !hasAccess('database',$table)) {
        if (hasAccess('db.table.list.owned',$table)) {
            if ($where == null) {
                $where = [];
            }
            $where['user_id'] = $user_id;
        } else {
            http_response_code(403);
            echo json_encode(['message'=>'You have no access to the database table '.$table,'highlight'=>'db-list-data','resource'=>$table]);
            die();
        }
    }

    $itemsPerPage = 15;
    $start = $page * $itemsPerPage;
    
    // Initialize base select statement based on count flag
    if ($count) {
        $tableWithPrefix = $useDB->prefixTable($table);//db engine doesn't detect table name
        $selectWhat = "count($tableWithPrefix.id) as count";
    } else {
        $selectWhat = "$table.*";
    }
    
    $orderBy = empty($data['sort']) ? null : $data['sort'];
    $sortOrder = empty($data['order']) ? '' : $data['order'];

    // Separate arrays for different types of conditions
    $userWheres = [];  // For user access restrictions
    $searchWheres = []; // For search conditions
    $whereValues = [];
    $searchStr = "%$search%";

    // Handle user access restrictions first
    if (!$allAccess && !hasAccess('db.table.get_all',$table) && !hasAccess('database',$table)) {
        if (hasAccess('db.table.list.owned',$table)) {
            $userWheres[] = "$table.user_id = :owned_user_id";
            $whereValues['owned_user_id'] = $user_id;
        }
    }

    // Handle search conditions
    if ($search != null) {
        $columns = $useDB->fetchAllQuery("SHOW columns FROM $table;");
        $i = 0;
        foreach($columns as $column) {
            $searchWheres[] = $table.'.'.$column['Field']." LIKE :searchstr$i";
            $whereValues['searchstr'.$i] = $searchStr; 
            $i++;
        }
    }

    // Handle additional where conditions
    if ($where != null) {
        $whereIdx = 0;
        $columns = $useDB->fetchAllQuery("SHOW columns FROM $table;");
        foreach($columns as $column) {
            if (isset($where[$column['Field']])) {
                $fieldName = "whval$whereIdx";
                $userWheres[] = $table.'.'.$column['Field']." LIKE :$fieldName";
                $whereValues[$fieldName] = $where[$column['Field']];
                $whereIdx++;
            }
        }
    }

    $joins = '';
    if (!empty($data['resourcetype'])) {
        $resource = $useDB->getByName('resource', $data['resourcetype']);
        $parameters = json_decode($resource['parameters']);
        $i = 0;
        foreach($parameters as $parameter) {
            $alias = "a$i";
            if ($parameter->type == 'db_row') {
                $dbtable = $parameter->options->dbtable;
                $titlefield = $parameter->options->titlefield;
                $idfield = $parameter->options->idfield;
                $translateOptions = $parameter->options->translate_options;
                $joins .= "LEFT JOIN ".$dbtable." AS $alias ON ".$table.".".$parameter->name." = ".$alias.".".$idfield." ";
                $translateAlias = $alias."t";
                if ($translateOptions) {
                    $joins .= "LEFT JOIN translation $translateAlias ON $alias.$titlefield = $translateAlias.translation_key AND $translateAlias.language='".$GLOBALS['language']."' ";
                }
                if ($search != null) {
                    if ($translateOptions) {
                        $searchWheres[] = "$translateAlias.translation LIKE :searchstr$i";
                    } else {
                        $searchWheres[] = "$alias.$titlefield LIKE :searchstr$i";
                    }
                    $whereValues['searchstr'.$i] = $searchStr; 
                }
               if (!$count) {
                    if ($translateOptions) {
                        $selectWhat .= ", $translateAlias.translation as ".$parameter->name."_resolved"; 
                    } else {
                        $selectWhat .= ", $alias.$titlefield as ".$parameter->name."_resolved";
                    }
                }
            }
            if ($parameter->type == 'translated_string' || $parameter->type == 'translated_formatted_string') {
                $joins .= "LEFT JOIN translation AS $alias ON ".$table.".".$parameter->name." = ".$alias.".translation_key AND ".$alias.".language = '".$GLOBALS['language']."' ";
                if ($search != null) {
                    $searchWheres[] = "$alias.translation LIKE :searchstr$i";
                    $whereValues['searchstr'.$i] = $searchStr; 
                }
                if (!$count) {
                    $selectWhat .= ", $alias.translation as ".$parameter->name."_translated";
                }
            }
            $i++;
        }
    }

    // Combine conditions properly
    $whereStatement = "";
    if (!empty($userWheres) || !empty($searchWheres)) {
        $finalWheres = [];
        
        // User restrictions must be ANDed
        if (!empty($userWheres)) {
            $finalWheres[] = '(' . implode(' AND ', $userWheres) . ')';
        }
        
        // Search conditions should be ORed
        if (!empty($searchWheres)) {
            $finalWheres[] = '(' . implode(' OR ', $searchWheres) . ')';
        }
        
        // Combine all conditions with AND
        $whereStatement = "WHERE " . implode(' AND ', $finalWheres);
    }

    $orderByStatement = !empty($orderBy) ? "ORDER BY ".$orderBy : '';
    $sql = "SELECT $selectWhat FROM $table $joins $whereStatement $orderByStatement $sortOrder LIMIT $start, $itemsPerPage";
    
    if ($count) {
        $result = $useDB->getByQuery($sql, $whereValues);
        $result = $result['count'];
    } else {
        $result = $useDB->getAllByQuery($sql, $whereValues);
    }
    
    echo json_encode($result);
?>