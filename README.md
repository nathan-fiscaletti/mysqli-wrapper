# MySqli Wrapper

### A simple ORM for MySql and PHP.

> composer require nafisc/mysqli-wrapper

## How to use?

See [the Wiki](https://github.com/nathan-fiscaletti/mysqli-wrapper/wiki) for information on how to use MySqli Wrapper!

```php

use \MySqliWrapper\DataBase as DB;
use \MySqliWrapper\MySqlConnection as Connection;

DB::register(
    'main',
    new Connection(
        'localhost',
        'username',
        'password',
        'database',
        3306
    )
);

$user = DB::get('main')
            ->table('users')
            ->where('name', '=', 'Nathan')
            ->get();

$user->age = 10;
$user->save();

```