# Connections

MySqliWrapper has a specific class for storing each of your MySql connections. 

## Creating a new [MySqlConnection](https://github.com/nathan-fiscaletti/mysqli-wrapper/blob/master/src/MySqliWrapper/MySqlConnection.php)

To register a new MySqlConnection use the [Database](../src/MySqliWrapper/Database.php) class. This class is used to store Connections as well as give some rudimentary access to generating [Models](../src/MySqliWrapper/Model.php) and [QueryBuilders](../src/MySqliWrapper/QueryBuilder.php).

```php
use MySqliWrapper\Database as DB;

DB::register(
    [
        // The name of the connection. This will be
        // used later within your Models and QueryBuilders 
        // to determine which Connection to use.
        'name' => 'main', 

        // The rest of the configuration options.
        // All of these are required.
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'password',
        'database' => 'database',
        'port' => 3306,
        'charset' => 'utf8'
    ]
);
```

## Retrieving the MySqlConnection

You can later retrieve this connection using the same class.

```php
$connection = DB::get('main');
```

## Other Features

You can also use the Database class to create [QueryBuilders](./queries.md) as well as [Models](./models.md), however they are limited to only using the FIRST Connection. (See [Database::first()](../src/MySqliWrapper/Database.php#L46))