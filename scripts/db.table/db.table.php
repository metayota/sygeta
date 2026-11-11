<?php namespace Metayota; include_once('../metayota/library.php');
include_once('../translation-service/translation-service.php');
    requireAccess('db.table.get_all',$data['table']);
    
    $table = $data['table'];
    $where = !empty($data['where']) ? $data['where'] : null;

    // If no resourcetype given, fall back to simple fetch
    if (empty($data['resourcetype'])) {
        echo json_encode($db->fetchAll($table, $where));
        die();
    }

    $selectWhat = "$table.*";
    $joins = '';
    $whereValues = [];

    // Build JOINs and SELECT fields for translated/resolved columns
    $resource = $db->getByName('resource', $data['resourcetype']);
    $parameters = json_decode($resource['parameters']);
    $i = 0;
    foreach ($parameters as $parameter) {
        $alias = "a$i";
        if ($parameter->type == 'db_row') {
            $dbtable = $parameter->options->dbtable;
            $titlefield = $parameter->options->titlefield;
            $idfield = $parameter->options->idfield;
            $translateOptions = $parameter->options->translate_options;
            $joins .= "LEFT JOIN $dbtable AS $alias ON $table.{$parameter->name} = $alias.$idfield ";
            $translateAlias = $alias . "t";
            if ($translateOptions) {
                $joins .= "LEFT JOIN translation $translateAlias ON $alias.$titlefield = $translateAlias.translation_key AND $translateAlias.language = '{$GLOBALS['language']}' ";
                $selectWhat .= ", $translateAlias.translation AS {$parameter->name}_resolved";
            } else {
                $selectWhat .= ", $alias.$titlefield AS {$parameter->name}_resolved";
            }
        } else if ($parameter->type == 'translated_string' || $parameter->type == 'translated_formatted_string') {
            $joins .= "LEFT JOIN translation AS $alias ON $table.{$parameter->name} = $alias.translation_key AND $alias.language = '{$GLOBALS['language']}' ";
           // $selectWhat .= ", $alias.translation AS {$parameter->name}_translated";
            $selectWhat .= ", $alias.translation AS {$parameter->name}";
        }
        $i++;
    }

    // Build WHERE clause from $where array
    $whereStatement = '';
    if ($where != null) {
        $whereParts = [];
        $columns = $db->fetchAllQuery("SHOW columns FROM $table;");
        $whereIdx = 0;
        foreach ($columns as $column) {
            if (isset($where[$column['Field']])) {
                $fieldName = "whval$whereIdx";
                $whereParts[] = "$table.{$column['Field']} = :$fieldName";
                $whereValues[$fieldName] = $where[$column['Field']];
                $whereIdx++;
            }
        }
        if (!empty($whereParts)) {
            $whereStatement = 'WHERE ' . implode(' AND ', $whereParts);
        }
    }

    $sql = "SELECT $selectWhat FROM $table $joins $whereStatement";

    echo json_encode($db->getAllByQuery($sql, $whereValues));
?>