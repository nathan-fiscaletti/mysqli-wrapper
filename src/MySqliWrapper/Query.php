<?php

namespace MySqliWrapper;

class Query
{
    /**
     * The query.
     *
     * @var string
     */
    private $query = '';

    /**
     * The binds.
     *
     * @var array
     */
    private $binds = [];

    /**
     * The bind types.
     *
     * @var string
     */
    private $bindTypes = '';

    /**
     * The name of the connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * Construct the QueryBuilder.
     *
     * @param string $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Append to or set the Query.
     *
     * @param string $query
     * @param bool   $append
     *
     * @return \MySqliWrapper\Query
     */
    public function raw($query, $append = true)
    {
        if ($append) {
            $this->query .= $query;
        } else {
            $this->query = $query;
        }

        return $this;
    }

    /**
     * Add a value to the query binds.
     *
     * @param mixed $value
     *
     * @return \MySqliWrapper\Query
     */
    public function withQueryParameter($value)
    {
        $this->binds[] = $value;
        $this->bindTypes .= mysqliwrapper__asQueryBindType($value);

        return $this;
    }

    /**
     * Adds a list of values to the query binds.
     *
     * @param array $parameters
     *
     * @return \MySqliWrapper\Query
     */
    public function withQueryParameters($parameters)
    {
        foreach ($parameters as $parameter) {
            $this->binds[] = \MySqliWrapper\DataBase::get($this->connection)->escapeString($parameter);
            $this->bindTypes .= mysqliwrapper__asQueryBindType($parameter);
        }

        return $this;
    }

    /**
     * Retrieve the raw query.
     *
     * @var bool
     *
     * @return string
     */
    public function getRawQuery($withNewLines = true)
    {
        if (! $withNewLines) {
            return str_replace(PHP_EOL, ' ', $this->query);
        }

        return $this->query;
    }

    /**
     * Retrieve the Binds array for the query.
     *
     * @param bool $withBindTypes
     *
     * @return array
     */
    public function getBinds($withBindTypes = true)
    {
        $binds = $this->binds;
        if ($withBindTypes) {
            array_unshift($binds, $this->bindTypes);
        }

        return $binds;
    }

    /**
     * Retrieve the bind types.
     *
     * @return string
     */
    public function getBindTypes()
    {
        return $this->bindTypes;
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
        array_unshift($binds, $this->bindTypes);
        $results = \MySqliWrapper\DataBase::get(
            $this->connection
        )->execute(
            $this, $return
        );

        return $results;
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

        if (count($all) > 0) {
            return $all[0];
        }
    }
}
