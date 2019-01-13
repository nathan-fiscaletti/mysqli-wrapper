# Mysqli Wrapper

### A simple ORM for PHP.

> composer require nafisc/mysqli-wrapper


Latest Version: v1.7

## Creating a MySql Connection

```php
use MySqliWrapper\ConnectionManager as DB;

// Register your database.
DB::register(
    'main', 
    new SqlConnection(
        'localhost',
        'root',
        'password',
        'vrazo',
        3306
    )
);

$connection = DB::get('main');
```

## Executing Sql

```php

// Returns an array of results.
$results = $connection->executeSql(
    // The query
    "SELECT * FROM `servers` WHERE `ipv4` = ? AND `id` > ?",

    // The Query Binds
    [
        // The query bind types in order of occurance
        'si',

        // The query binds in order of occurance
        '111.111.111.111'
        10
    ],

    // True if you want a result returned.
    true
);

```

## Query Builder

```php
echo $connection->table('servers')
           ->select(['servers.id', 'servers.ipv4', 'server_stats.uptime'])
           ->leftJoin('server_stats', function($join) {
               $join->on('servers.id', '=', 'server_stats.server_id');
           })
           ->where('servers.ipv4', '=', '1.1.1.1')
           ->when(
               $onlyHighUptime, 
               function($query) {
                   $query->and('server_stats.uptime', '>', 500);
               },
               function($query) {
                   $query->and('server_stats.uptime', '<', 500);
               }
           )
           ->getRawQuery();

// . . . 

// Output:
/*

SELECT
servers.id,
servers.ipv4,
server_stats.uptime
FROM `servers`
LEFT JOIN `server_stats`
ON servers.id = server_stats.server_id
WHERE servers.ipv4 = ?
AND uptime < ?

*/
```

## Models

```php
use MySqliWrapper\Model as Model;

// Create a model
class Server extends Model
{
    // The table that this model is associated with.
    protected $table      = 'servers';

    // The connection to use.
    protected $connection = 'main';
}

// $server will be instance of Server class.
$server = Server::get()->where('ipv4', '=', '1.1.1.1')->limit(1)->first();

// ... OR ...

// $server will be instance of \MySqliWrapper\Model class.
$server = DB::get('main')->model('servers')->get()->where('ipv4', '=', '1.1.1.1')->limit(1)->first();

// . . .

$server->ipv4 = '2.2.2.2';
$server->save();
```