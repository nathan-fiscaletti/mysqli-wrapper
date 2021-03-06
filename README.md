# MySqli Wrapper

### A simple [ORM](https://en.wikipedia.org/wiki/Object-relational_mapping) for MySql and PHP.

> composer require nafisc/mysqli-wrapper

[![StyleCI](https://styleci.io/repos/139458381/shield?style=flat)](https://styleci.io/repos/139458381)
[![Latest Stable Version](https://poser.pugx.org/nafisc/mysqli-wrapper/v/stable?format=flat)](https://packagist.org/packages/nafisc/mysqli-wrapper)
[![Total Downloads](https://poser.pugx.org/nafisc/mysqli-wrapper/downloads?format=flat)](https://packagist.org/packages/nafisc/mysqli-wrapper)
[![Latest Unstable Version](https://poser.pugx.org/nafisc/mysqli-wrapper/v/unstable?format=flat)](https://packagist.org/packages/nafisc/mysqli-wrapper)
[![License](https://poser.pugx.org/nafisc/mysqli-wrapper/license?format=flat)](https://packagist.org/packages/nafisc/mysqli-wrapper)

## How to use?

See [the documentation](./docs) for information on how to use MySqli Wrapper!

## Quick Example

```php
    use \MySqliWrapper\Database as DB;

    DB::register(
        [
            'name'     => 'main', 
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'password',
            'database' => 'database',
            'port'     => 3306,
            'charset'  => 'utf8',
        ]
    );

    $user = DB::get('main')
            ->table('users')
            ->where('name', '=', 'Nathan')
            ->first();

    $user->age = 10;
    $user->save();
```
