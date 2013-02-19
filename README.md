# WEBLAMP442 Assignment 2, Option 1

Developer: Kent Dietz

## Project Requirements

NOTE: Assignment allowed selection of MYSQLI or PDO ... this project works with MYSQLI.

PHP provides Mysqli driver to interact with databases. The driver lacks a simplified OO interface. There are a number of popular open source frameworks
out there that provides this interface, such as doctrine dbal, Zend_Db etc. We want to challenge ourselves and learn in a hard way. So we are going re-invent the wheel
by implementing a simplified version of DBAL (Database access layer) by wrapping Mysqli, this object provides the following methods:

```
class Connect
- constructor   , takes either an array of database parameters or a configuration object that represents database parameters.
- connect()     , connect to the database
- disconnect()  , shutdowns established database connection
- select()       , return the result of a select query. This function may return an array of rows, an array of models that represent the tables or rows.
- update()      , execute the update sql statement. It should throw an exception if the query fails to execute.
- delete()      , execute the delete sql statement. It should throw an exception if the query fails to execute.
- insert()      , execute insert sql statement. It should return last insert id if operation is successful, or throw an exception if the operation fails.
```

Unit tests are provided for the ConnectConfiguration and Connect classes.  To maintain isolation of the tests a setUp routine is used to destroy and recreate
the database and ini file used for each Connect test.

Let's assume we try to interact with a table User, user table schema looks like:
```sql
CREATE TABLE `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB
```

And my client code (or unit test) to interact with this table will something look:

```php
use Db\Connection;

$db = new Connection( array(
                'host'     => 'localhost',
                'username' => 'foo',
                'password' => 'password',
                'dbname'   => 'mydatabase'
            ) );

// connect to database, expect to
try {
    $db->connect();
} catch(\RuntimeException $e) {
    die( 'fails to establish database connection, error:'. $e->getMessage() );
}

$insertSql = "'INSERT INTO Post (firstname, lastname) VALUES ('John', 'Smith')";
$rowId = $db->insert($insertSql);

$newRow = $db->select( sprintf("SELECT * FROM User WHERE id = %d", $rowId ) );

// Should have at least one row
assert(count($newRow) > 0);

```

## Project files structure

```
 - Src
   - Connect.php (database connect object)
   - ConnectConfiguration.php (configuration object)
 -Tests
   - Conf
	   - phpunit.xml (phpunit configuration)
   - Src
     - ConnectConfigurationTest.php (tests for ConnectConfiguration class)
     - ConnectTest.php (tests for Connect class)
   - Bootstrap.php (takes care of handling autoloading, setting error levels, other test configuration work)
 - Bootstrap.php   (takes care of autoloading)
 - composer.json   (Sets up autoloading, dependencies (phpunit))
 - .travis.yml     (Travis CI setup file)
 - README.md       (This file, project informationWhat this project is, how to run composer to install dependencies, how to run unit tests
```

## Project Details

### Enlisting

Follow these steps to build put the project in a working state:

1. Subscribe to the github repository: https://github.com/kedietz/WEBLAMP442_ASSIGNMENT2_OPTION1
2. Run composer from the repository root to setup autoloading and get phpunit
   * composer update -v -o (slower, but downloads required to download correct phpunit)
   * composer install -v -o (faster, but download)
3. Run tests:
   * Make sure <repository root>/Vendors/bin is on your path
   * run: phpunit --config Tests\Conf\phpunit.xml Tests
`
4. Run code coverage
   * phpunit --coverage-html ./report tests
   * view report/index.html (NOTE: this will also show "Vendors" directory coverage ... please ignore, did not figure out how to remove it from project).\

