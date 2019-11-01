# Queries & Query Builders

There are several ways in which you can make queries in MySqliWrapper.

## Queries

**The first way in which you can make queries is using a [Query](../src/MySqliWrapper/Query.php) object.**

When you initialize a Query object you must provide the name of the connection the Query will run on.
The Query object can only execute raw queries, with the added benefit of being able to use query parameters.

The following example will fetch the user where `user_id` is `3` from the `main` connection and return it as an array.

```php

use MySqliWrapper\Query;

$query = new Query('main');
$query->raw('SELECT * FROM `users` WHERE `users_id` = ?');
$query->withQueryParameter(3); 

$result = $query->fetchFirst();
```

**There are several other ways you can execute a Query object.**

1. Execute (without result).
```php
$query->execute();
```

2. Retrieve a list of all rows that match the query.
```php
$query->fetch();
```

3. Retrieve only the first row matching the query.
```php
$query->fetchFirst();
```

4. Execute directly through a Connection.
```php
// Change `true` to `false` if you should not receive a result.
// This defaults to false.
$connection->execute($query, true);
```

## Query Builders

**The second way in which you can execute Queries is using a [QueryBuilder](../src/MySqliWrapper/QueryBuilder.php)**

With a QueryBuilder you still have access to all of the same features as a Query, however you are now provided with functions for generating the query itself.

The following code demonstrates a simple example of the same query as above, but using a QueryBuilder instead.

```php
$connection->table('users')
           ->select('*')
           ->where('user_id', 3)
           ->fetchFirst();
```

> See [QueryBuilder](../src/MySqliWrapper/QueryBuilder.php) for a list of all functions provided by the QueryBuilder.

**When using a QueryBuilder, there are two more functions added for retrieving the result.**

They can be used as follows: 

1. Retrieve an instance of [\MySqliWrapper\Model](../src/MySqliWrapper/Model.php) for the Table.
```php
$connection->table('users')
           ->select('*')
           ->where('user_id', 3)
           ->first();
```

2. Retrieve a list of instances of [\MySqliWrapper\Model](../src/MySqliWrapper/Model.php) for the Table.
```php
$connection->table('users')
           ->select('*')
           ->where('user_id', 3)
           ->get();
```

> See [Models](./models.md).