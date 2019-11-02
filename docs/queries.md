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

You can retrieve a query builder using `$connection->table('sometable')`. You can then start building your query with this object.

The following code demonstrates a simple example of the same query as above, but using a QueryBuilder instead.

```php
$connection->table('users')
           ->select('*')
           ->where('user_id', 3)
           ->fetchFirst();
```

**When using a QueryBuilder, there are two more functions added for retrieving the result.**

They can be used as follows: 

1. Retrieve an instance of [\MySqliWrapper\Model](../src/MySqliWrapper/Model.php) for the Table.
```php
$queryBuilder->first();
```

2. Retrieve a list of instances of [\MySqliWrapper\Model](../src/MySqliWrapper/Model.php) for the Table.
```php
$queryBuilder->get();
```

> You can also still use all of the functions that come from the `Query` class.

## Using a QueryBuilder

For each of the following examples; assume we have a Query Builder in the variable `$qb` that was created with the following code:

```php
$qb = $connection->table('people');
```

After you have finished building a Query using the Query Builder, you can execute it using one of the functions documented earlier on this page.

> Including what's listed here, there are a lot of other use cases for the Query Builder. Look at the [`\MySqliWrapper\QueryBuilder`](../src/MySqliWrapper/QueryBuilder.php) class for a full list of these functions.

### Inserting Data 

- PHP

   ```php
   $qb->insert([
       'name' => 'nathan', 
       'age' => 24
   ]);

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   INSERT INTO `people`
   (`name`,`age`)
   VALUES (?,?)
   ```

### Deleting Data

- PHP

   ```php
   $qb->delete()
      ->where('name', '=', 'nathan')
      ->orWhere('age', '>', 5);

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   DELETE FROM `people`
   WHERE `name` = ?
   OR `age` > ?
   ```

### Updating Data

- PHP

   ```php
   $qb->update([
       'age' => 25,
       'phone' => '1234567890',
   ])->where('name', '=', 'John');

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   UPDATE `people` SET
   `age` = ?,`phone` = ?
   WHERE `name` = ?
   ```

### Incrementing a Column

- PHP

   ```php
   $qb->increment('friends')
      ->where('name', '=', 'john');

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   UPDATE `people` SET
   `friends` = `friends` + ?
   WHERE `name` = ?
   ```

- You can also

   * Provide a number to increment by, by default this function will increment the number by `1`.
   
      ```php
      $qb->increment('friends', 10);
      ```

   * Provider aditional `update` data

      ```php
      $qb->increment('friends', 10, ['age' => 25]);
      ```

### Retrieving Data

- PHP

   ```php
   $qb->select(['name', 'age']);

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   SELECT
   `name`, `age`
   FROM `people`
   ```

- You can also

   * Provide `'*'` instead of an array of elements to select.

      ```php
      $qb->select('*');
      ```

   * Provide a custom select string

      ```php
      $qb->select('`name`, `people.age` as years_old');
      ```

### Joining other Tables

- PHP

   ```php
   $qb->select(['people.*', 'games.name'])
      ->leftJoin('games', function($join) {
          $join->on('games.id', '=', '`people.game_id`');
      });

   echo $qb->getRawQuery();
   ```

- Results In

   ```sql
   SELECT
   `people.*`, `games.name`
   FROM `people`
   LEFT JOIN `games`
   ON `games.id` = `people.game_id`
   ```

- You can also

    * Normal `join` with `$qb->join($table, $join);`
    * Outer `join` with `$qb->outerJoin($table, $join);`
    * Inner `join` with `$qb->innerJoin($table, $join);`
    * Left Outer `join` with `$qb->leftOuterJoin($table, $join);`
    * Right `join` with `$qb->rightJoin($table, $join);`
    * Right Outer `join` with `$qb->rightOuterJoin($table, $join);`
    * Cross `join` with `$qb->crossJoin($table);`
