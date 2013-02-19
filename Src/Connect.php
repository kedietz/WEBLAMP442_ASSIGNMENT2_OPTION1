<?php

class Connect {
  /**
   * configuration information for connection
   */
  protected $_config;

  /**
   * mysqli object to use for connection
   */
   protected $_connection;

  /**
   * Connect object constructor
   * @param ConnectConfiguration
   *
   */
  public function __construct( $config ) {
    if (strcmp(gettype($config), "object") != 0 ||
        strcmp(get_class($config), "ConnectConfiguration") != 0) {
      throw new InvalidArgumentException("Cannot create Connect; parameter must be a ConnectConfiguration object");
    }

    // Validate that connect configuration is adequate to continue
    $config->validate();

    $this->_config = $config;
    $this->_connection = null;
  }

  public function __destruct() {
    if ($this->_connection != null) { // clean up connection
       $this->_connection->close();
    }
    $this->_connection = null;
  }

  /**
   * Connect to database
   */
  public function connect() {
    $this->_connection = new mysqli(
      $this->_config->getHost(),
      $this->_config->getUserName(),
      $this->_config->getPassword(),
      $this->_config->getDBName(),
      $this->_config->getPort(),
      $this->_config->getSocket());
    if ($this->_connection->connect_error) {
      throw new RuntimeException("Error connecting to database. Error: (" . $$this->_connection->connect_errno . ") - " . $$this->_connection->connect_error);
    }
  }

  /**
   * Connect to database
   */
  public function disconnect() {
    if ($this->_connection == null) {
      throw new RuntimeException("disconnect() called before connect");
      }
    if (strcmp(get_class($this->_connection), "mysqli") != 0) {
      throw new UnexpectedValueException("Internal error: mysqli connection is invalid");
    }
    $this->_connection->close();
    $this->_connection = null;
  }

  /**
   * run SELECT command
   */
  public function select($query) {
    $connection = $this->_connection;
    if ($connection == null) {
      throw new RuntimeException("need to connect before calling select()");
    }

    // verify that query is an SELECT command; does not check any other syntax
    if (strncasecmp($query, "select ", 7) != 0) {
      throw new InvalidArgumentException("Query is not an 'SELECT' command: ". $query);
    }

    $result = $connection->query($query);
    if ($result == false) {
      throw new RuntimeException("SELECT failed. Error (".$connection->errno.")[".$connection->error."][".$query."]");
    }
    return $result;
  }

  /**
   * Run UPDATE command
   *
   * It should throw an exception if the query fails to execute.
   */
  public function update($query) {
    $connection = $this->_connection;
    if ($connection == null) {
      throw new RuntimeException("need to connect before calling update()");
    }
    // verify that query is an UPDATE command; does not check any other syntax
    if (strncasecmp($query, "update ", 7) != 0) {
      throw new InvalidArgumentException("Query is not an 'UPDATE' command: ". $query);
    }

    $result = $connection->query($query);
    if ($result != true) {
      throw new RuntimeException("UPDATE  failed. Error (".$connection->errno.")[".$connection->error."][".$query."]");
    }


    // throw if query fails
  }

  /**
   * Run DELETE command
   */
  public function delete($query) {
    $connection = $this->_connection;
    // validate that connection is open
    if ($connection == null) {
      throw new RuntimeException("need to connect before calling delete()");
    }

    // verify that query is an DELETE command; does not check any other syntax
    if (strncasecmp($query, "delete ", 7) != 0) {
      throw new InvalidArgumentException("Query is not an 'DELETE' command: ". $query);
    }

    $result = $connection->query($query);
    if ($result != true) {
      throw new RuntimeException("DELETE failed. Error (".$connection->errno.")[".$connection->error."][".$query."]");
    }
  }

  /**
   * Perform an INSERT operation to connected database
   * @param query - SQL query to use to perform the operation
   *
   */
  public function insert($query) {
    $connection = $this->_connection;
    // validate that connection is open
    if ($connection == null) {
      throw new RuntimeException("need to connect before calling insert()");
    }

    // verify that query is an INSERT command; does not check any other syntax
    if (strncasecmp($query, "insert ", 7) != 0) {
      throw new InvalidArgumentException("Query is not an 'INSERT' command: ". $query);
    }
    $result = $connection->query($query);
    if ($result != true) {
      throw new RuntimeException("INSERT failed. Error (".$connection->errno.")[".$connection->error."][".$query."]");
    }
  }
}
?>


