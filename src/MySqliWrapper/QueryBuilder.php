<?php

namespace MySqliWrapper;

class QueryBuilder extends \MySqliWrapper\Query
{
    /**
     * The table on which to be building this query.
     *
     * @var string $table
     */
    protected $table;

    /**
     * The configuration to pass to any generated models.
     *
     * @var array $config
     */
    private $config;

    /**
     * Construct the QueryBuilder.
     *
     * @param string $connection
     * @param string $table
     * @param array  $config
     */
    public function __construct($connection, $table, $config = [])
    {
        parent::__construct($connection);
        $this->table = $table;
        $this->config = $config;
    }

    /**
     * Delete data.
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function delete()
    {
        return $this->raw("DELETE FROM `$this->table`".PHP_EOL);
    }

    /**
     * Insert data.
     *
     * @param array $data
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function insert($data)
    {
        $query = "INSERT INTO `$this->table`".PHP_EOL;

        $propResult = '(';
        $valResult = '(';
        foreach ($data as $property => $value)
        {
            $propResult .= ($propResult == '(')
                ? "$property"
                : ",$property";
            
            $valResult .= ($valResult == '(')
                ? "?"
                : ",?";
            $this->withQueryParameter($value);
        }
        $propResult .= ')';
        $valResult .= ')';

        $query .= "$propResult".PHP_EOL."VALUES $valResult".PHP_EOL;

        return $this->raw($query);
    }

    /**
     * Retrieve the last insert ID from this QueryBuilders Connection object.
     *
     * @return int
     */
    public function lastInsertId()
    {
        return \MySqliWrapper\DataBase::get($this->connection)->lastInsertId();
    }

    /**
     * Update data.
     *
     * @param array $data
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function update($data)
    {
        $query = "UPDATE `$this->table` SET".PHP_EOL;

        $result = '';
        foreach ($data as $property => $value) {
            $result .= ($result == '')
                ? "$property = ?"
                : ",$property = ?";
            
            $this->withQueryParameter($value);
        }

        $query .= $result.PHP_EOL;

        return $this->raw($query);
    }

    /**
     * Increment a column by a given amount and
     * optionally update other columns as well.
     *
     * @param string $column
     * @param int    $amount
     * @param array  $update
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function increment($column, $amount = 1, $update = [])
    {
        $query .= "UPDATE `$this->table` SET".PHP_EOL;
        $result = "$column = $column + ?";
        $this->withQueryParameter($amount);
        foreach ($update as $property => $value)
        {
            $result .= ",$property = ?";
            $this->withQueryParameter($value);
        }
        $query .= $result.PHP_EOL;

        return $this->raw($query);
    }

    /**
     * Select specified columns.
     *
     * @param array|string $what
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function select($what = '*')
    {
        return $this->raw('SELECT'.PHP_EOL.
                        mysqliwrapper__selectableToString($what).PHP_EOL.
                        "FROM `$this->table`".PHP_EOL);
    }

    /**
     * Allows you to write where exists SQL clauses.
     *
     * The whereExists method accepts a Closure argument,
     * which will receive a query builder instance allowing
     * you to define the query that should be placed inside
     * of the "exists" clause.
     *
     * @param \closure $closure
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function whereExists($closure)
    {
        $this->raw('WHERE EXISTS ( '.PHP_EOL);
        $closure($this);

        return $this->raw(')'.PHP_EOL);
    }

    /**
     * Add a where clause.
     *
     * @param string $property
     * @param string $operator
     * @param string $value
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function where($property, $operator, $value)
    {
        $this->withQueryParameter($value);

        return $this->raw("WHERE $property $operator ?".PHP_EOL);
    }

    /**
     * Add an OR clause.
     *
     * @param string $property
     * @param string $operator
     * @param string $value
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function or($property, $operator, $value)
    {
        $this->withQueryParameter($value);

        return $this->raw("OR $property $operator ?".PHP_EOL);
    }

    /**
     * Add an AND clause.
     *
     * @param string $property
     * @param string $operator
     * @param string $value
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function and($property, $operator, $value)
    {
        $this->withQueryParameter($value);

        return $this->raw("AND $property $operator ?".PHP_EOL);
    }

    /**
     * Join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function join($table, $join)
    {
        $this->raw("JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Outer join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function outerJoin($table, $join)
    {
        $this->raw("OUTER JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Outer join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function innerJoin($table, $join)
    {
        $this->raw("INNER JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Left join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function leftJoin($table, $join)
    {
        $this->raw("LEFT JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Left outer join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function leftOuterJoin($table, $join)
    {
        $this->raw("LEFT OUTER JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Right join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function rightJoin($table, $join)
    {
        $this->raw("RIGHT JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Right outer join a table.
     *
     * @param string   $table
     * @param \closure $join
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function rightOuterJoin($table, $join)
    {
        $this->raw("RIGHT OUTER JOIN `$table`".PHP_EOL);
        $join($this);

        return $this;
    }

    /**
     * Cross join a table.
     *
     * @param string   $table
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function crossJoin($table)
    {
        return $this->raw("CROSS JOIN `$table`".PHP_EOL);
    }

    /**
     * Add a WHERE clause.
     *
     * @param string $where
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function whereRaw($where)
    {
        return $this->raw("WHERE $where".PHP_EOL);
    }

    /**
     * Add an OR clause.
     *
     * @param string $where
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function orRaw($where)
    {
        return $this->raw("OR $where".PHP_EOL);
    }

    /**
     * Add an AND clause.
     *
     * @param string $where
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function andRaw($where)
    {
        return $this->raw("AND $where".PHP_EOL);
    }

    /**
     * Executes the given Closure when the first parameter is true.
     * If the first parameter is false, the Closure will not be executed.
     * You may pass another Closure as the third parameter to the when method.
     * This Closure will execute if the first parameter evaluates as false. 
     *
     * @param bool     $value
     * @param \closure $true
     * @param \closure $false
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function when($value, $true, $false = null)
    {
        if ($value) {
            if (! is_null($true)) {
                $true($this);
            }
        } else {
            if (! is_null($false)) {
                $false($this);
            }
        }

        return $this;
    }

    /**
     * Adds an ON clause.
     *
     * @param string $property
     * @param string $operator
     * @param string $value
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function on($property, $operator, $value)
    {
        return $this->raw("ON $property $operator $value".PHP_EOL);
    }

    /**
     * Order the results by the specified column.
     *
     * @param string $column
     * @param string $direction
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function orderBy($column, $direction = 'ASC')
    {
        return $this->raw("ORDER BY $column $direction".PHP_EOL);
    }

    /**
     * Offset the results returned.
     *
     * @param int $offset
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function offset($offset)
    {
        return $this->raw("OFFSET $offset".PHP_EOL);
    }

    /**
     * Offset the results returned.
     *
     * @param int $offset
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function skip($offset)
    {
        return $this->offset($offset);
    }

    /**
     * Limit the number of results.
     *
     * @param int $limit
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function limit($limit)
    {
        return $this->raw("LIMIT $limit".PHP_EOL);
    }

    /**
     * Limit the number of results.
     *
     * @param int $limit
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function take($limit)
    {
        return $this->limit($limit);
    }

    /**
     * Retrieves all models matching the select query.
     *
     * @param string $modelClass
     *
     * @return \MySqliWrapper\Model[]
     */
    public function get($modelClass = \MySqliWrapper\Model::class)
    {
        $results = $this->execute(true);

        $models = [];

        foreach ($results as $result) {
            if ($this instanceof \MySqliWrapper\Model) {
                $modelClass = get_called_class();
            }
            $model = new $modelClass(
                $this->connection,
                $this->table,
                array_merge(
                    ['data' => $result],
                    $this->config
                )
            );
            if ($model->dataExists($model->id_column)) {
                $model->id = $model->dataFor($model->id_column, false);
            } else {
                $model->savable(false);
            }
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Retrieves the first model matching the select query.
     *
     * @return \MySqliWrapper\Model|null
     */
    public function first()
    {
        $all = $this->get();
        if (sizeof($all) > 0)
            return $all[0];

        return null;
    }

    /**
     * Handle the debug print for this object.
     */
    public function __debugInfo() {
        return [
            'query' => $this->getRawQuery(false),
            'bind_types' => $this->getBindTypes(),
            'binds' => $this->getBinds(false),
            'connection' => $this->connection,
            'table' => $this->table,
        ];
    }
}
