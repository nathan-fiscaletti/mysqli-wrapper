# Models

To create a Model for a table, you have a few options. MySqliWrapper does not require you to create a class in order to use a model, however the option is available. 

## Using a Class

To create a class based model, create a new class and extend the [\MySqliWrapper\Model ](../src/MySqliWrapper/Model.php) class.

You will need to override the `$connection` property and the `$table` property. These tell the system what Connection to use for the Model and what table the model represents.

```php
class User extends \MySqliWrapper\Model
{
    protected $connection = 'main';
    protected $table = 'users';
}
```

To fetch a specific instance you can use the class statically. There are two static functions for Models, `find(property, operator, value)` and `all()`.

- `find(property, operator, value)`

   Returns an instance of the Model as a QueryBuilder with the specified `where` clause applied.

- `all()`

   Returns an instance of the Model as a QueryBuilder with the `select * from table` query applied.

```php
$user = User::find('id', '=', '3')->first();
$allUsers = User::all()->get();
```

## Using a Connection

To use a connection, you simply need to call the `->model` function on your [MySqlConnection](../src/MySqliWrapper/MySqlConnection.php) object.

The following code will return an instance of [\MySqliWrapper\Model ](../src/MySqliWrapper/Model.php) which can then be used as a QueryBuilder to retrieve an instance.

```php
$model = DataBase::get('main')->model('users');
$user = $model->select()
              ->where('id', '=', '3')
              ->andWhere('name', '=', 'nathan')
              ->get();
```

> Alternately, you can use `Database::model('users')` which will use the first Connection registered in the DataBase class. (See [Creating a new MySqlConnection](./connections.md#creating-a-new-mysqlconnection))

## Modifying the Model Instance

You can then treat the model as a normal object to make changes to it. To commit the changes, use the `->save()` function.

```php
$user->name = 'Bob';
$user->age  = 14;
$user->save();
```

> Note: Changes made using the QueryBuilder features of a Model will not be reflected when the `->save()` function is called. The Model portion of the object and the QueryBuilder portion operate independently of each other. 