<?php

namespace MySqliWrapper;

class QueryBuilder
{
    /**
     * The name of the connection to use.
     * 
     * @var string $connection
     */
    protected $connection;

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
     * The query that will be executed.
     * 
     * @var string $query
     */
    private $query = '';

    /**
     * The bind type string.
     * 
     * @var string $bindStr
     */
    private $bindStr = '';

    /**
     * The bind values.
     * 
     * @var array $binds
     */
    private $binds = [];

    /**
     * Construct the QueryBuilder.
     * 
     * @param string $connection
     * @param string $table
     * @param array  $config
     */
    public function __construct($connection, $table, $config = [])
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->config = $config;
    }

    /**
     * Set the current query.
     * 
     * @param string $query
     */
    public function raw($query)
    {
        $this->query = $query;
    }

    /**
     * Append to the current query.
     * 
     * @param string $query
     */
    public function appendRaw($query)
    {
        $this->query .= $query.PHP_EOL;
    }

    /**
     * Delete data.
     * 
     * @return \MySqliWrapper\QueryBuilder
     */
    public function delete()
    {
        $this->query .= "DELETE FROM `$this->table`".PHP_EOL;

        return $this;
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
        $this->query .= "INSERT INTO `$this->table`".PHP_EOL;

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
            $this->binds[] = $value;
            $this->bindStr .= mysqliwrapper__asQueryBindType($value);
        }
        $propResult .= ')';
        $valResult .= ')';

        $this->query .= "$propResult".PHP_EOL."VALUES $valResult".PHP_EOL;

        return $this;
    }

    /**
     * Retrieve the last insert ID from this QueryBuilders Connection object.
     */
    public function lastInsertId()
    {
        return \MySqliWrapper\ConnectionManager::get($this->connection)->lastInsertId();
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
        $this->query = "UPDATE `$this->table` SET".PHP_EOL;

        $result = '';
        foreach ($data as $property => $value) {
            $result .= ($result == '')
                ? "$property = ?"
                : ",$property = ?";
            
            $this->binds[] = $value;
            $this->bindStr .= mysqliwrapper__asQueryBindType($value);
        }

        $this->query .= $result.PHP_EOL;

        return $this;
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
        $this->query .= 'SELECT '.PHP_EOL.
                        mysqliwrapper__selectableToString($what).PHP_EOL.
                        "FROM `$this->table`".PHP_EOL;

        return $this;
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
        $this->query .= 'WHERE EXISTS ( '.PHP_EOL;
        $closure($this);
        $this->query .= ')'.PHP_EOL;

        return $this;
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
        $this->query .= "WHERE $property $operator ?".PHP_EOL;
        $this->binds[] = $value;
        $this->bindStr .= mysqliwrapper__asQueryBindType($value);

        return $this;
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
        $this->query .= "OR $property $operator ?".PHP_EOL;
        $this->binds[] = $value;
        $this->bindStr .= mysqliwrapper__asQueryBindType($value);

        return $this;
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
        $this->query .= "AND $property $operator ?".PHP_EOL;
        $this->binds[] = $value;
        $this->bindStr .= mysqliwrapper__asQueryBindType($value);

        return $this;
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
        $this->query .= "JOIN `$table`".PHP_EOL;
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
        $this->query .= "OUTER JOIN `$table`".PHP_EOL;
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
        $this->query .= "INNER JOIN `$table`".PHP_EOL;
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
        $this->query .= "LEFT JOIN `$table`".PHP_EOL;
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
        $this->query .= "LEFT OUTER JOIN `$table`".PHP_EOL;
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
        $this->query .= "RIGHT JOIN `$table`".PHP_EOL;
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
        $this->query .= "RIGHT OUTER JOIN `$table`".PHP_EOL;
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
        $this->query .= "CROSS JOIN `$table`".PHP_EOL;

        return $this;
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
        $this->query .= "WHERE $where".PHP_EOL;

        return $this;
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
        $this->query .= "OR $where".PHP_EOL;

        return $this;
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
        $this->query .= "AND $where".PHP_EOL;

        return $this;
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
        $this->query .= "ON $property $operator $value".PHP_EOL;

        return $this;
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
        $this->query .= "ORDER BY $column $direction".PHP_EOL;

        return $this;
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
        $this->query .= "OFFSET $offset".PHP_EOL;

        return $this;
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
        $this->query .= "LIMIT $limit".PHP_EOL;

        return $this;
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
     * Execute the query built by this QueryBuilder.
     * 
     * @param bool $return
     * 
     * @return mixed
     */
    public function execute($return = false)
    {
        $binds = $this->binds;
        array_unshift($binds, $this->bindStr);
        $results = \MySqliWrapper\ConnectionManager::get(
            $this->connection
        )->executeSql(
            $this->query,
            $binds,
            $return
        );

        return $results;
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
     * Retrieves an array of rows.
     * 
     * @return arrray
     */
    public function fetch()
    {
        return $this->execute(true);
    }

    /**
     * Retrieves the first row.
     * 
     * @return arrray|null
     */
    public function fetchFirst()
    {
        $all = $this->execute(true);

        if (sizeof($all) > 0)
            return $all[0];

        return null;
    }

    /**
     * Retrieve the raw query.
     * 
     * @return string
     */
    public function getRawQuery()
    {
        return $this->query;
    }
}