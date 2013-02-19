<?php
  class ConnectConfiguration {


    /**
     * @string
     * File specification for configuration file.  Must be a string that resolves to a absolute or relative path to a file.
     */
    private $_iniFile;

    /**
     * @string
     * Can be either a host name or an IP address. Passing the NULL value or the string "localhost" to this parameter, the local host is assumed.
     * Prepending host by p: opens a persistent connection.
     */
    private $_host;

    /**
     * @string
     * The MySQL user name
     */
    private $_username;

    /**
     * @string
     * The MySQL password user name, can be NULL
     */
    private $_password;

    /**
     * @string
     * Database to be used when performing queries.
     */
    private $_dbname;

    /**
     * @int
     * Port number to attempt to connect to the MySQL server.
     */
    private $_port;

    /**
     * @string
     * Socket name or named pipe that should be used.
     */
    private $_socket;

    /**
     *
     */
   public function __construct($iniFile) {
      $this->_iniFile = $iniFile;
   }

   public function init() {
      // validate parameter
      if ($this->_iniFile == null) {
        throw new InvalidArgumentException("Invalid argument: Configuration file must not be null");
      }
      if (strcmp(gettype($this->_iniFile), "string") != 0) {
        throw new InvalidArgumentException("Invalid argument: Configuration file must be a string, detected '" . gettype($this->_iniFile)."'");
      }

      // initialize object state using mysqli defaults from
      $this->_host = ini_get("mysqli.default_host");
      $this->_username = ini_get("mysqli.default_user");
      $this->_password = ini_get("mysqli.default_pw");
      $this->_dbname = "";
      $this->_port = ini_get("mysqli.default_port");
      $this->_socket = ini_get("mysqli.default_socket");
   }

   public function read() {
      if (!file_exists($this->_iniFile)) {
        throw new InvalidArgumentException("Invalid argument, initialization file " . $this->_iniFile . " does not exist");
      }

      // read entire file
      $contents = file_get_contents($this->_iniFile);

      $jsonINI = json_decode($contents, true);

      foreach ($jsonINI as $key => $value) {
        switch($key) {
          case "host":
            $this->_host = $value;
            break;
          case "username":
            $this->_username = $value;
            break;
          case "password":
            $this->_password = $value;
            break;
          case "dbname":
            $this->_dbname = $value;
            break;
          case "port":
            $this->_port = $value;
            break;
          case "socket":
            $this->_socket = $value;
            break;
          default:
            break;
        }
      }
      $this->validate();
    }

    public function write( $configArray ) {
      $json = json_encode($configArray);
      file_put_contents($this->_iniFile, $json);
    }

    public function validate() {
      if (empty($this->_host)) {
        throw new InvalidArgumentException("Invalid argument: 'Host' must be set. Neither default nor configuration contained a value");
      }
      if (empty($this->_username)) {
        throw new InvalidArgumentException("Invalid argument: 'Username' must be set. Neither default nor configuration contained a value");
      }
      if (empty($this->_dbname)) {
        throw new InvalidArgumentException("Invalid argument: 'Database' must be set. Neither default nor configuration contained a value");
      }
    }

    public function getHost() {
      return $this->_host;
    }

    public function getUserName() {
      return $this->_username;
    }

    public function getDBName() {
      return $this->_dbname;
    }

    public function getPassword() {
      return $this->_password;
    }

    public function getPort() {
      return $this->_port;
    }

    public function getSocket() {
      return $this->_socket;
    }
  }
?>