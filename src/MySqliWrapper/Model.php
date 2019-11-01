<?php

namespace MySqliWrapper;

use MySqliWrapper\QueryBuilder;

class Model extends QueryBuilder
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
            $result = $this->update($this->data)
                           ->where($this->$column_prefix.$this->id_column, '=', $this->id)
                           ->execute();
            $this->reset();
            return $result;
        } else {
            if ($this->insert($this->data)->execute()) {
                $this->id = $this->lastInsertId();
                $this->reset();
                return true;
            }
        }

        $this->reset();
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
     * Set the value for a column.
     * 
     * @param string $column
     * @param mixed  $value
     * @param bool   $withPrefix
     */
    public function setProperty($column, $value, $withPrefix = true) {
        $this->data[($withPrefix ? $this->column_prefix : '').$column] = $value;
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
     * Retrieve all instances of this Model.
     */
    public static function all() {
        $class = get_called_class();

        return (new $class())->select();
    }

    /**
     * Find all instances of this Model matching the specified criteria.
     * 
     * @param $property
     * @param $operator
     * @param $value
     * 
     * @return \MySqliWrapper\Model
     */
    public static function find($property, $operator, $value) {
        $class = get_called_class();

        return (new $class())->select()->where($property, $operator, $value);
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
        $this->setProperty($property, $value);
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
        return $this->dataFor($property);
    }

    /**
     * Handle the debug print for this Model.
     */
    public function __debugInfo()
    {
        $properties = [];
        foreach ($this->data as $key => $val) {
            $properties[str_replace($this->column_prefix, '', $key)] = $val;
        }

        return array_merge([
            'id' => $this->id,
            'connection' => $this->connection,
            'table' => $this->table,
            'column_prefix' => $this->column_prefix,
            'id_column' => $this->id_column,
            'savable' => $this->savable,
        ], $properties);
    }
}
