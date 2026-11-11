<?php namespace Metayota; include_once('../metayota/library.php');  
$table = $parameter->options->dbtable;
$column = $parameter->options->idfield;

// Use prepared statement to avoid SQL injection
$sql = "SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";

$result = $serverDB->getByQuery($sql, ['table'=>$table, 'column'=>$column]);

// $result will be an array of rows; we expect one row
if (!empty($result)) {
    // Return the DATA_TYPE of the column
    return $result['COLUMN_TYPE'];
} else {
    // Column or table not found
    return 'INT';
}
?>