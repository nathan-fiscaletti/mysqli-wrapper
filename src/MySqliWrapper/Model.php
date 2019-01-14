<?php

namespace MySqliWrapper;

class Model
{
    /**
     * The data.
     *
     * @var array
     */
    private $data = [];

    /**
     * The configuration.
     *
     * @var array
     */
    private $config;

    /**
     * Whether or not this instance is savable.
     *
     * @var bool
     */
    private $savable = true;

    /**
     * The table to which this Model belongs.
     *
     * @var string
     */
    protected $table;

    /**
     * The connection name to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * The column prefix.
     *
     * @var string
     */
    protected $column_prefix;

    /**
     * The name of the ID column.
     *
     * @var mixed
     */
    public $id_column = 'id';

    /**
     * The ID for this instance.
     *
     * @var mixed
     */
    public $id;

    /**
     * Construct the Model.
     *
     * @param string $connection
     * @param string $table
     * @param array  $config
     */
    public function __construct($connection = null, $table = null, $config = [])
    {
        if (! is_null($connection)) {
            $this->connection = $connection;
        }

        if (! is_null($table)) {
            $this->table = $table;
        }

        $this->config = $config;

        foreach ($config as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            } else {
                throw new \Exception("Unknown configuration entry '$property' during ".get_class($this).' generation.');
            }
        }
    }

    /**
     * Retrieve a QueryBuilder for the Model.
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    private function queryBuilder()
    {
        return new \MySqliWrapper\QueryBuilder(
            $this->connection,
            $this->table,
            $this->config
        );
    }

    /**
     * Set whether or not this instance is Savable.
     *
     * @param bool $value
     */
    public function savable($value)
    {
        $this->savable = $value;
    }

    /**
     * Save any changes.
     *
     * @return bool
     */
    public function save()
    {
        if (! $this->savable) {
            throw new \Exception('Cannot save '.get_class($this).' instance due to unknown ID.');
        }

        if ($this->saved()) {
            return
                $this->update($this->data)
                    ->where($this->id_column, '=', $this->id)
                    ->execute();
        } else {
            if ($this->insert($this->data)->execute()) {
                $this->id = $this->lastInsertId();

                return true;
            }
        }

        return false;
    }

    /**
     * Check if any changes have been made.
     *
     * @return bool
     */
    public function saved()
    {
        return ! is_null($this->id);
    }

    /**
     * Retrieve the data for a column.
     *
     * @param string $column
     * @param bool   $withPrefix
     *
     * @return mixed
     */
    public function dataFor($column, $withPrefix = true)
    {
        return $this->data[($withPrefix ? $this->column_prefix : '').$column];
    }

    /**
     * Check if a column exists.
     *
     * @param string $column
     * @param bool   $withPrefix
     *
     * @return bool
     */
    public function dataExists($column, $withPrefix = true)
    {
        return isset($this->data[($withPrefix ? $this->column_prefix : '').$column]);
    }

    /**
     * Used for forwarding QueryBuilder functions to this Models QueryBuilder.
     *
     * @param string $function
     * @param array  $parameters
     *
     * @return mixed
     */
    public static function __callStatic($function, $parameters)
    {
        $class = get_called_class();

        return (new $class())->{$function}(...$parameters);
    }

    /**
     * Used for forwarding QueryBuilder functions to this Models QueryBuilder.
     *
     * @param string $function
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($function, $parameters)
    {
        return $this->queryBuilder()->{$function}(...$parameters);
    }

    /**
     * Sets a value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return mixed
     */
    public function __set($property, $value)
    {
        $this->data[$this->column_prefix.$property] = $value;
    }

    /**
     * Retrieves a value.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->data[$this->column_prefix.$property];
    }
}
