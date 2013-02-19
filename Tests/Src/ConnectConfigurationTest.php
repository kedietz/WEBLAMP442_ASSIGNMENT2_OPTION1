<?php

  class ConnectConfigurationTest extends PHPUnit_Framework_TestCase {


    public static function Configuration_Provider() {
      return array(
        array("localhost", "user", "passwd", "dbname", null, null, "", "", "", "./test.cfg"),
        array("localhost", "user", "passwd", "dbname", null, null, "InvalidArgumentException", "", "", null),
      );
    }

    /**
     * @test
     * @dataProvider Configuration_Provider
     *
     */
    public function test_WriteConfiguration($host, $username, $password, $dbname, $port, $socket, $expectedInitException, $expectedWriteException, $expectedReadException, $iniFile ) {

      if (file_exists($iniFile)) { // remove inifile if it exists, to keep tests pure.
        unlink($iniFile);
      }

      // create configuration object
      $config = new ConnectConfiguration($iniFile);
      try {
        // Initialize configuration object to default values
        $config->init();
      } catch (Exception $e) {
        // If we got an exception ... was it expected in init?
        $this->assertEquals($expectedInitException, get_class($e));
        return;
      }

      // Create configuration associative array
      $configArray = array();
      if ($host != null) {
        $configArray["host"] = $host;
        $expectedHost = $host;
      } else {
        $expectedHost = $config->getHost();
      }
      if ($username != null) {
        $configArray["username"] = $username;
        $expectedUsername = $username;
      } else {
        $expectedUsername = $config->getUsername();
      }
      if ($password != null) {
        $configArray["password"] = $password;
        $expectedPassword = $password;
      } else {
        $expectedPassword = $config->getPassword();
      }

      if ($dbname != null) {
        $configArray["dbname"] = $dbname;
        $expectedDBName = $dbname;
      } else {
        $expectedDBName = $config->getDBName();
      }

      if ($port != null) {
        $configArray["port"] = $port;
        $expectedPort = $port;
      } else {
        $expectedPort = $config->getPort();
      }

      if ($socket != null) {
        $expectedSocket = $config->getSocket();
        $configArray["socket"] = $socket;
        $expectedSocket = $socket;
      } else {
        $expectedSocket = $config->getSocket();
      }

      try {
        $config->write($configArray);
      } catch (Exception $e) {
        echo "\nwrite:".get_class($e)."\n";
        $this->assertEquals($expectedWriteException, get_class($e));
      }

      $this->assertFileExists($iniFile);

      try {
        $config->read();
      } catch (Exception $e) {
        $this->assertEquals($expectedReadException, get_class($e));
      }

      $this->assertEquals($expectedHost, $config->getHost());
      $this->assertEquals($expectedUsername, $config->getUsername());
      $this->assertEquals($expectedPassword, $config->getPassword());
      $this->assertEquals($expectedDBName, $config->getDBName());
      $this->assertEquals($expectedPort, $config->getPort());
      $this->assertEquals($expectedSocket, $config->getSocket());
    }
  }

?>