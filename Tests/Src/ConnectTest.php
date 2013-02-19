<?php

class ConnectTest extends PHPUnit_Framework_TestCase {

  /**
   * Delete and recreate a database and table to support tests
   *
   */
  protected function setUp() {
    $server_link = null;
    try {
      $server_link = new mysqli('localhost', 'root', '');
      if (!$server_link) {
        echo "Error: setUp() - server connect failed.".PHP_EOL;
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp() - server connect threw: ".$e->getMessage().PHP_EOL;
      return;
    }

    try {
      $result = $server_link->query('DROP DATABASE  IF EXISTS testconnect');
      if (!$result) {
        echo "Error: setUp - DROP failed: ". $server_link->error.PHP_EOL;
        $server_link->close();
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp(0): ".$e->getMessage().PHP_EOL;
      $server_link->close();
      return;
    }

    try {
      $result = $server_link->query('CREATE DATABASE testconnect;');
      if (!$result) {
        echo "Error: setUp - CREATE DATABASE failed: ". $server_link->error.PHP_EOL;
        $server_link->close();
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp(0): ".$e->getMessage().PHP_EOL;
      $server_link->close();
      return;
    }
    $server_link->close();

    $db_link = null;
    try {
      $db_link = new mysqli('localhost', 'root', '', 'testconnect');
      if (!$db_link) {
        echo "Error: setUp() - database connect failed.".PHP_EOL;
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp() - database connect threw: ".$e->getMessage().PHP_EOL;
      return;
    }

    try {
      $query = "CREATE TABLE test ";
      $fields = "id INT NOT NULL AUTO_INCREMENT";
      $fields .= ", first_name varchar(127)";
      $fields .= ", last_name varchar(127)";
      $fields .= ", email_address varchar(127)";
      $fields .= ", PRIMARY KEY(id)";
      $query .= "(".$fields.") ENGINE = MyISAM;";

      $result = $db_link->query($query);
      if (!$result) {
        echo "Error: setUp - CREATE failed: ". PHP_EOL.$query.PHP_EOL.$db_link->error.PHP_EOL;
        $db_link->close();
        return;
      }
      $db_link->close();
    } catch (Exception $e) {
      echo "Error: setUp(2): ".$e->getMessage().PHP_EOL;
      $db_link->close();
      return;
    }

    // Initialize config
    if (file_exists("test.ini")) {
      unlink("testconnect.ini");
    }
    try {
      $config = new ConnectConfiguration("testconnect.ini");
      $config->write(["host"=>"localhost", "username"=>"root", "password"=>"", "dbname"=>"testconnect"]);
    } catch(Exception $e) {
      $this->assertFalse(3);
    }
  }

  /**
   * Destroy database and ini file after all tests are completed.
   */
  public static function tearDownAfterClass() {
    $server_link = null;
    try {
      $server_link = new mysqli('localhost', 'root', '');
      if (!$server_link) {
        echo "Error: setUp() - server connect failed.".PHP_EOL;
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp() - server connect threw: ".$e->getMessage().PHP_EOL;
      return;
    }

    try {
      $result = $server_link->query('DROP DATABASE  IF EXISTS testconnect');
      if (!$result) {
        echo "Error: setUp - DROP failed: ". $server_link->error.PHP_EOL;
        $server_link->close();
        return;
      }
    } catch (Exception $e) {
      echo "Error: setUp(0): ".$e->getMessage().PHP_EOL;
      $server_link->close();
      return;
    }
    $server_link->close();

    // Initialize config
    if (file_exists("testconnect.ini")) {
      unlink("testconnect.ini");
    }
  }

  /**
   * Test Connect constructor.
   *
   *
   */
  public function test_Constructor() {
    $connection = null;
    $config = null;

    // Try null configuration ... should fail
    try {
      $connection = new Connect($config);
      $this->assertFalse(1); // should not get here
    } catch (Exception $e) {
      $this->assertEquals("InvalidArgumentException", get_class($e));
    }

    // Try file name ... should fail
    $config = "testconnect.ini";
    try {
      $connection = new Connect($config);
      $this->assertFalse(2); // should not get here
    } catch (Exception $e) {
      $this->assertEquals("InvalidArgumentException", get_class($e));
    }

    // create config object
    try {
      $config = new ConnectConfiguration("testconnect.ini");
    } catch(Exception $e) {
      $this->assertFalse(3);
    }

    // Try incompletely initialized config object .. should fail
    try {
      $connection = new Connect($config);
      $this->assertFalse(4);
    } catch (Exception $e) {
      $this->assertEquals("InvalidArgumentException", get_class($e));
    }

    // read configuration
    try {
      $config->read();
    } catch (Exception $e) {
      $this->assertFalse(5);
    }

    // Finally, create Connect object, this should work.
    try {
      $connection = new Connect($config);
    } catch (Exception $e) {
      $this->assertFalse(6);
    }

    try {
      $connection->connect();
    } catch (Exception $e) {
      $this->assertFalse(7);
    }

    try {
      $connection->insert("INSERT INTO test (first_name, last_name, email_address) VALUES ('fred', 'flintstone', 'ff@bedrock.org')");
    } catch (Exception $e) {
      $this->assertFalse(8);
    }

    try {
      $result = $connection->select("SELECT * FROM test");
      if (strcmp(get_class($result), "mysqli_result") != 0) {
        $this->assertFalse(10);
      }
      $this->assertEquals($result->num_rows, 1);
      $this->assertEquals($result->field_count, 4);
      // Not going to validate field values ... that is another test
    } catch (Exception $e) {
      $this->assertFalse($e->getMessage());
    }

    try {
      $connection->disconnect();
    } catch (Exception $e) {
      $this->assertFalse(9);
    }
  }

  /**
   * Test query commands (INSERT/SELECT/UPDATE/DELETE)
   * Do a basic sanity test for the supported commands.
   *
   */
  public function test_query() {
    // create config object
    $config = new ConnectConfiguration("testconnect.ini");
    $config->read();
    $connection = new Connect($config);
    $connection->connect();

    $values = [['fred', 'flintstone', 'ff@bedrock.org'],
               ['betty', 'rubble', 'betrub@coldmail.org'],
               ['sylvester', 'slate', 'mrslate@slateinc.com'],
               ['gary', 'granite', 'gargran@hollyrock.net']];

    $querybase = "INSERT INTO test (first_name, last_name, email_address) VALUES ";
    $count = count($values);
    for ($i = 0; $i < $count; $i++) {
      $query = $querybase."('".$values[$i][0]."','".$values[$i][1]."','".$values[$i][2]."')";
      $connection->insert($query);
    }
    $result = $connection->select("SELECT * FROM test");
    $this->assertEquals($result->num_rows, 4);
    $this->assertEquals($result->field_count, 4);

    while ($obj = $result->fetch_object()) {
      $this->assertEquals($obj->first_name, $values[$obj->id-1][0]);
      $this->assertEquals($obj->last_name, $values[$obj->id-1][1]);
      $this->assertEquals($obj->email_address, $values[$obj->id-1][2]);
    }

    $connection->update("UPDATE test SET first_name='george' WHERE last_name='slate'");
    $values[2][0] = 'george';

    $result = $connection->select("SELECT * FROM test");
    $this->assertEquals($result->num_rows, 4);
    $this->assertEquals($result->field_count, 4);

    while ($obj = $result->fetch_object()) {
      $this->assertEquals($obj->first_name, $values[$obj->id-1][0]);
      $this->assertEquals($obj->last_name, $values[$obj->id-1][1]);
      $this->assertEquals($obj->email_address, $values[$obj->id-1][2]);
    }

    $connection->delete("DELETE from test WHERE last_name = 'granite'");

    $result = $connection->select("SELECT * FROM test");
    $this->assertEquals($result->num_rows, 3);
    $this->assertEquals($result->field_count, 4);

    while ($obj = $result->fetch_object()) {
      $this->assertEquals($obj->first_name, $values[$obj->id-1][0]);
      $this->assertEquals($obj->last_name, $values[$obj->id-1][1]);
      $this->assertEquals($obj->email_address, $values[$obj->id-1][2]);
    }

    $connection->disconnect();

  }
}

?>