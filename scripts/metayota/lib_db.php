<?php

namespace Metayota;

function mapParameters($v, $value = null) {
    $vd = str_replace('.','_',$v);
    // Check if this is for a WHERE clause and value is NULL
    if ($value === null) {
        return $v . " IS NULL";
    }
    return($v."=:".$vd);
}

function mapSetParameters($v) {
    $vd = str_replace('.','_',$v);
    return('`'.$v.'`'."= :set_".$vd);
}

class PDORepository {
    
    public \PDO $connection;
    public string $table_prefix;
    public string $database_name;
    public array $stmtCache = [];
    
    function __construct($login = NULL) {
        if ($login == NULL) {
            $login = array();
            $login['username'] = $GLOBALS['DB_USERNAME'];
            $login['password'] = $GLOBALS['DB_PASSWORD'];
            $login['host'] = $GLOBALS['DB_HOST'];
            $login['db'] = $GLOBALS['DB_NAME'];
        }
        $this->database_name = $login['db'];
        $this->connection = $this->getConnection($login);
        if (!empty($login['table_prefix'])) {
            $this->table_prefix = $login['table_prefix'];
        } else {
            $this->table_prefix = getConfig('db_table_prefix','');
        }
    }
    
    public function prefixTable($table) {
        $tablePrefix = getConfig('table_mapping');
        if (!empty($tablePrefix) && !empty($tablePrefix[$table])) {
            return $tablePrefix[$table];
        }
        return $this->table_prefix.$table;
    }
    
    public function getByName($table,$name) {
        return $this->fetchByName($table,$name);
    }
    
    public function getById($table,$id) {
        return $this->fetchById($table,$id);
    }
    
    public function getByQuery($query,$arguments=array()) {
        return $this->fetchQuery($query,$arguments);
    }
    
    public function getColumnByQuery($query,$arguments=[]) {
        $result = $this->fetchQuery($query,$arguments);
        if (empty($result)) {
            return null;
        } else {
            return reset($result);
        }
    }
    
    public function getColumnByName($table,$column,$name) {
        return $this->getColumn($table,$column,['name'=>$name]);
    }
    
    public function getColumnByID($table,$column,$name) {
        return $this->getColumn($table,$column,['id'=>$name]);
    }

public function insertOrUpdate($table, $values = [], $duplicateColumn = 'id') {
    // Check if record exists based on the duplicate column
    $whereCondition = [$duplicateColumn => $values[$duplicateColumn]];
    $existing = $this->fetch($table, $whereCondition);
    
    if ($existing) {
        // Record exists, update it
        return $this->update($table, $values, $whereCondition);
    } else {
        // Record doesn't exist, insert it
        return $this->insert($table, $values);
    }
}
    
    public function get($table,$where=array()) {
        return $this->fetch($table,$where);
    }
    
    public function getAllByQuery($sql,$arguments=array()) {
        return $this->fetchAllQuery($sql,$arguments);
    }
    
    public function getAll($table,$where=array(),$attributes=array()) {
        return $this->fetchAll($table,$where,$attributes);
    }
    
    public function addPrefixToSQL($sql) {
        $prefix = $this->table_prefix;
        
        $pattern = '/\b(FROM|JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|INSERT INTO|INSERT IGNORE INTO|ALTER TABLE)\s+(`?)([a-zA-Z0-9_]+)(`?)\b/i';
        $replacedTables = [];
        $replacedSql = preg_replace_callback($pattern, function ($matches) use ($prefix, &$replacedTables) {
            $keyword = $matches[1];
            $backtickStart = $matches[2];
            $table = $matches[3];
            $backtickEnd = $matches[4];
            
            // Check if the table name already has the prefix
            if (strpos($table, $prefix) === 0) {
                return $matches[0];
            }
            
            $replacedTables[] = $table;
            return $keyword . ' ' . $backtickStart . $this->prefixTable($table) . $backtickEnd;
        }, $sql);
        
        $replacedTables = array_unique($replacedTables);
        foreach($replacedTables as $replacedTable) {
            $replacedSql = str_replace(" ".$replacedTable.'.', " ".$this->prefixTable($replacedTable).'.', $replacedSql);
            $replacedSql = str_replace(" `".$replacedTable.'`.', " `".$this->prefixTable( $replacedTable )."`.", $replacedSql);
        }
        
        return $replacedSql;
    }
    
    private function getConnection($login){
        $username = !empty($login['db_username']) ? $login['db_username'] : $login['username'];
        $password = !empty($login['db_password']) ? $login['db_password'] : $login['password'];
        $host = !empty($login['db_host']) ? $login['db_host'] : $login['host'];
        $db = $login['db'];
        $port = !empty($login['db_port']) ? $login['db_port'] : "3306"; 
  
        $connection = new \PDO("mysql:dbname=$db;port=$port;host=$host;charset=utf8mb4", $username, $password);
        return $connection;
    }
    
    public function fetchAllQuery($sql, $args=[]) {
        if (!empty($this->table_prefix)) {
            $sql = $this->addPrefixToSQL($sql);
        }
        
        $stmt = $this->queryNoPrefix($sql, $args);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }
    
    public function fetchQuery($sql, $args=[]) {
        if (!empty($this->table_prefix)) {
            $sql = $this->addPrefixToSQL($sql);
        }
        $stmt = $this->queryNoPrefix($sql, $args);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result;
    }
    
    public function queryNoPrefix($sql, $args=[]){
        $stmt = $this->getPreparedStatement($sql);
        if (!($result = $stmt->execute($this->getSQLValues($args)))) {
            echo "Error in statement $sql: ";
            var_dump($stmt->errorInfo());
        }
        return $stmt;
    }
    
    public function query($sql, $args=[]){
        if (!empty($this->table_prefix)) {
            $sql = $this->addPrefixToSQL($sql);
        }
        
        $stmt = $this->getPreparedStatement($sql);
        if (!($result = $stmt->execute($this->getSQLValues($args)))) {
            echo "Error in statement $sql: ";
            var_dump($stmt->errorInfo());
        }
        $stmt->closeCursor();
        
        return $stmt;
    }
    
    public function getId($table, $name) {
        $table = $this->prefixTable($table);
        $tableEscaped = $this->quoteIdentifier($table);
        $res = $this->getPreparedStatement("SELECT id FROM $tableEscaped WHERE name=:name");
        $res->execute(array('name'=>$name));
        $result = $res->fetch(\PDO::FETCH_ASSOC)['id'];
        return $result;
    }
    
    public function fetchWithParameters($query,$parameters) {
        $res = $this->getPreparedStatement($query);
        $res->execute($this->getSQLValues($parameters));
        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getPreparedStatement($query) {
        $queryHash = md5($query);
        if (!isset($this->stmtCache[$queryHash])) {
            $this->stmtCache[$queryHash] = $this->connection->prepare($query);
        }
        return $this->stmtCache[$queryHash];
    }
    
    public function fetchOneWithParameters($query,$parameters) {
        $res = $this->getPreparedStatement($query);
        $res->execute($this->getSQLValues($parameters));
        return $res->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function fetchAll($table,$where=array(),$attributes=array()) {
        $table = $this->prefixTable($table);
        $whereStatement = '';
        if ($where != null) {
            $whereStatement = $this->getWhereStatement($where);
        }
        $moreStatement = "";
        if (isset($attributes['orderBy'])) {
            $moreStatement.= " ORDER BY ".$this->quoteIdentifier($attributes['orderBy']);
        }
        if (isset($attributes['orderByDesc'])) {
            $moreStatement.= " ORDER BY ".$this->quoteIdentifier($attributes['orderByDesc'])." DESC";
        }
        $fields = "*";
        if (isset($attributes['fields'])) {
            $fields = implode(', ', $attributes['fields']);
        }
        $tableEscaped = $this->quoteIdentifier($table);
        $res = $this->getPreparedStatement("SELECT $fields FROM $tableEscaped $whereStatement $moreStatement");
        if (!$res->execute($this->getSQLValuesForWhere($where))) {
            echo 'db.php:fetchAll() Error:';
            var_dump($res->errorInfo());
        }
        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getSetStatement($values) {
        $arrKeys = array_keys($values);
        $setStatements = array_map("\Metayota\mapSetParameters",$arrKeys);
        $setStatement = implode(", ",$setStatements);
        return $setStatement;
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function getWhereStatement($values) {
        if (!is_array($values)) {
            throw new \Exception('Must be countable.');
        }
        if (count($values) == 0) {
            return '';
        }
        
        $arrKeys = array_keys($values);
        $setStatements = [];
        
        foreach ($arrKeys as $key) {
            $setStatements[] = mapParameters($key, $values[$key]);
        }
        
        $setStatement = implode(" AND ",$setStatements);
        return 'WHERE '.$setStatement;
    }
    
    private function getSQLValues($values) {
        if (!$values) {
            return array();
        }
        $sqlValues = array();
        $arrKeys = array_keys($values);
        foreach($arrKeys as $arrKey) {
            $val = $values[$arrKey];
            $arrKey = str_replace('.','_',$arrKey);
            if (is_array($val) || is_object($val)) {
                $sqlValues[$arrKey] = (string) json_encode($val);
            } else if (is_bool($val)) {
                $sqlValues[$arrKey] = $val ? 1 : 0;
            } else if (is_null($val)) {
                $sqlValues[$arrKey] = NULL;
            } else {
                $sqlValues[$arrKey] = (string) $val;
            }
        }
        return $sqlValues;
    }
    
    // New method specifically for WHERE clauses that removes NULL values
    private function getSQLValuesForWhere($values) {
        if (!$values) {
            return array();
        }
        $sqlValues = array();
        $arrKeys = array_keys($values);
        foreach($arrKeys as $arrKey) {
            $val = $values[$arrKey];
            // Skip NULL values for WHERE clauses since they use "IS NULL" syntax
            if (is_null($val)) {
                continue;
            }
            $arrKey = str_replace('.','_',$arrKey);
            if (is_array($val) || is_object($val)) {
                $sqlValues[$arrKey] = (string) json_encode($val);
            } else if (is_bool($val)) {
                $sqlValues[$arrKey] = $val ? 1 : 0;
            } else {
                $sqlValues[$arrKey] = (string) $val;
            }
        }
        return $sqlValues;
    }
    
    public function deleteById($table,$id) {
        $table = $this->prefixTable($table);
        $table = preg_replace('/[^A-Za-z0-9_]+/', '', $table);
        $res = $this->getPreparedStatement( "DELETE FROM $table WHERE id=:id");
        if (!$res->execute(array(':id'=>$id))) {
            var_dump($res->errorInfo());
        }
    }
    
    public function delete($table,$where) {
        $table = $this->prefixTable($table);
        $whereStatement = $this->getWhereStatement($where);
        $tableEscaped = $this->quoteIdentifier($table);
        $sql = "DELETE FROM $tableEscaped $whereStatement";
        $stmt = $this->queryNoPrefix($sql,$this->getSQLValuesForWhere($where));
        return $stmt->rowCount() > 0;
    }
    
    public function fetchById($table,$id) {
        return $this->fetch($table,array('id'=>$id));
    }
    
    public function fetchByName($table,$name) {
        return $this->fetch($table,array('name'=>$name));
    }
    
    public function update($table,$values,$where='auto') {
        if ($where == 'auto') {
            $where = ['id'=>$values['id']];
        }
        if (is_numeric($where) || is_string($where)) {
            $where = ['id'=>$where];
        }
        $table = $this->prefixTable($table);
        $arrKeys = array_keys($values);
        $setStatements = array_map("\Metayota\mapSetParameters",$arrKeys);
        $setStatement = implode(", ",$setStatements);
        $prefixedKeys = array_map(function($key) {
            return 'set_' . $key;
        }, array_keys($values));
        
        $values = array_combine($prefixedKeys, $values);
        
        $whereStatement = $this->getWhereStatement($where);
        $sql = "UPDATE $table SET $setStatement $whereStatement";
        
        $res = $this->getPreparedStatement($sql);
        $sqlValues = array_merge($this->getSQLValues($values),$this->getSQLValuesForWhere($where));
        if (!$res->execute($sqlValues)) {
            var_dump($sql);
            var_dump($values);
            var_dump($res->errorInfo());
        }
    }
    
    function quoteIdentifier($identifier) {
        if (!preg_match('/^[a-zA-Z0-9$_\.]+$/', $identifier)) {
            throw new \Exception("Invalid identifier: $identifier");
        }
        return "`$identifier`";
    }
    
    public function getColumn($table,$column,$values=[]) {
        $table = $this->prefixTable($table);
        $whereStatement = $this->getWhereStatement($values);
        $columnEscaped = $this->quoteIdentifier($column);
        $tableEscaped = $this->quoteIdentifier($table);
        $res = $this->getPreparedStatement("SELECT $columnEscaped FROM $tableEscaped $whereStatement");
        $res->execute($this->getSQLValuesForWhere($values));
        $result = $res->fetch(\PDO::FETCH_ASSOC);
        if (!empty($result)) {
            return $result[$column];
        }
        return null;
    }
    
    public function fetch($table,$values) {
        $whereStatement = $this->getWhereStatement($values);
        $table = $this->prefixTable($table);
        $tableEscaped = $this->quoteIdentifier($table);
        $res = $this->getPreparedStatement($sql = "SELECT * FROM $tableEscaped $whereStatement");
        $res->execute($this->getSQLValuesForWhere($values));
        return $res->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function insertIgnore($table,$values) {
        $setStatement = $this->getSetStatement($values);
        $table = $this->prefixTable($table);
        $tableEscaped = $this->quoteIdentifier($table);
        $sql = "INSERT IGNORE INTO $tableEscaped SET $setStatement";
        $res = $this->getPreparedStatement($sql);
        
        // Add set_ prefix to the keys to match the SQL placeholders
        $prefixedKeys = array_map(function($key) {
            return 'set_' . str_replace('.','_',$key);
        }, array_keys($values));
        $prefixedValues = array_combine($prefixedKeys, $values);
        
        $result = $res->execute($this->getSQLValues($prefixedValues));
        if (!$result) {
            echo $sql;
            var_dump($res->errorInfo());
        }
    }

    
    public function insert($table,$values=[]) {
        $table = $this->prefixTable($table);
        $setStatement = $this->getSetStatement($values);
        $tableEscaped = $this->quoteIdentifier($table);
        $sql = "INSERT INTO $tableEscaped";
        if (!empty($values)) {
            $sql.= " SET $setStatement";
            
            // Add set_ prefix to the keys to match the SQL placeholders
            $prefixedKeys = array_map(function($key) {
                return 'set_' . $key;
            }, array_keys($values));
            $prefixedValues = array_combine($prefixedKeys, $values);
            
        } else {
            $sql.= " VALUES()";
            $prefixedValues = [];
        }
        
        $res = $this->getPreparedStatement($sql);
        $result = $res->execute($this->getSQLValues($prefixedValues)); // And here
        if (!$result) {
            echo $sql;
            var_dump($res->errorInfo());
        }
    }
    
    /**
    * Starts a new database transaction.
    */
    public function beginTransaction() {
        $this->connection->beginTransaction();
    }
    
    /**
    * Commits the current database transaction.
    */
    public function commit() {
        $this->connection->commit();
    }
    
    /**
    * Rolls back the current database transaction.
    */
    public function rollback() {
        $this->connection->rollBack();
    }
}
?>