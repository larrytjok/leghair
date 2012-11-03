<?php


class Mysqldb {

  private $connections = array();

  private $activeConnection = 0;

  /**
   * Queries which have been executed and the results cached for later,
   * primarily for use within the template engine
   */
  private $queryCache = array();

  /**
   * Data which has been prepared and then cached for later usage,
   * primarily within the template engine
   */
  private $dataCache = array();

  /**
   * Number of queries made during execution process
   */
  private $queryCounter = 0;

  /**
   * Record of the last query
   */
  private $last;

  private $registry;


  public function __construct( Registry $registry ) {
    $this->registry = $registry;
  }

  public function newConnection($host, $user, $password, $database) 
  {
    $this->connections[] = new mysqli( $host, $user, $password, $database);
    $connection_id = count($this->connections) - 1;
    if( mysqli_connect_errno() ) {
      trigger_error('Error connecting to host, ' .
        $this->connections[$connection_id]->error, E_USER_ERROR);
    }
    return $connection_id;
  }

  public function closeConnection() 
  {
    $this->connections[$this->activeConnection]->close();
  }

  public function setActiveConnection(int $connectionId) 
  {
    $this->activeConnection = $connectionId;
  }


  /**
   * Store a query result in the query cache for processing later
   * @param String - the query String
   * @return the a query cache id for the query result to retrive data later.
   */
  public function cacheQuery( $queryStr ) 
  {
    if (!$result = $this->connections[$this->activeConnection]->query( $queryStr )) {
      trigger_error('Error executing and caching query: ' . 
        $this->connections[$this->activeConnection]->error, E_USER_ERROR);
    } else {
      $this->queryCache[] = $result;
      return count($this->queryCache) - 1;
    }
  }

  public function numRowsFromCache( $query_cache_id ) 
  {
    return $this->queryCache[$query_cache_id]->num_rows;
  }

  public function resultsFromCache( $query_cache_id )
  {
    return $this->queryCache[$query_cache_id]->fetch_array(MYSQLI_ASSOC);
  }

  /**
   * Store some data in a cache for later use
   * @param the data
   * @return return a data cache id for the data to retrive data later
   */
  public function cacheData ( $data )
  {
    $this->dataCache[] = $data;
    return count($this->dataCache) - 1;
  }

  public function dataFromCache( $data_cache_id )
  {
    return $this->dataCache[$data_cache_id];
  }

  public function deleteRecords( $table, $condition, $limit ) 
  {
    $limit = ( $limit == '' ) ? '' : ' LIMIT ' . $limit;
    $delete = "DELETE FROM {$table} WHERE {$condition} {$limit}";
    $this->executeQuery($delete);
  }

  public function updateRecords( $table, $changes, $condition )
  {
    $update = "UPDATE " . $table . " SET ";
    foreach( $changes as $field => $value ) {
      $update .= "`" . $field . "`='{$value}',";
    }
    //remove the trailing ","
    $update = substr($update, 0, -1);
    if($condition) {
      $update .= " WHERE " . $condition;
    }
    $this->executeQuery($update);
    $return true;
  }

  public function insertRecords( $table, $data ) 
  {
    $fields = "";
    $values = "";

    foreach($data as $f => $v) {
      $fields .= "`$f`,";
      $values .= (is_numeric($v) && (intval($v) == $v)) ? $v."," : "'$v',";
    }
    //remove trailing ","
    $fields = substr($fields, 0, -1);
    $values = substr($values, 0, -1);

    $insert = "INSERT INTO $table ({$fields}) VALUES ({$values})";
    $this->executeQuery($insert);
    return true;
  }

  public function lastInsertID()
  {
    return $this->connections[$this->activeConnection]->insert_id;
  }

  public function executeQuery($queryStr)
  {
    if( !$result = $this->connections[$this->activeConnection]->query($queryStr)) {
      trigger_error('Error executing query: ' . $queryStr . " - " .
        $this->connections[$this->activeConnection]->error, E_USER_ERROR);
    } else {
      $this->last = $result;
    }
  }

  public function getRows()
  {
    return $this->last->fetch_array(MYSQLI_ASSOC);
  }

  public function numRows()
  {
    return $this->last->num_rows;
  }

  public function affectedRows()
  {
    return $this->last->affected_rows;
  }

  public function sanitizeData ($value) 
  {
    if (get_magic_quotes_gpc()) {
      $value = stripslashes($value);
    }
    //assuming php version > 4.3.0
    $value = $this->connections[$this->activeConnection]->real_escape_string($value);
    return $value;
  }

  public function __deconstruct() 
  {
    foreach($this->connections as $connection) {
      $connection->close();
    }
  }
}

?>
