<?php

namespace MySqliWrapper;

use MySqliWrapper\SqlConnection;
use MySqliWrapper\QueryBuilder;

class Model
{
    private $data = [];
    private $queryBuilder = null;
    private $config;
    private $savable = true;

    protected $table;
    protected $connection;
    protected $column_prefix;

    public $id_column = 'id';
    public $id;


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
                throw new \Exception("Unknown configuration entry '$property' during ".get_class($this)." generation.");
            }
        }
    }

    private function queryBuilder()
    {
        return new QueryBuilder(
            $this->connection,
            $this->table,
            $this->config
        );
    }

    public function savable($value)
    {
        $this->savable = $value;
    }

    public function save()
    {
        if (! $this->savable) {
            throw new \Exception("Cannot save ".get_class($this)." instance due to unknown ID.");
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

    public function saved()
    {
        return ! is_null($this->id);
    }

    public function dataFor($column, $withPrefix = true)
    {
        return $this->data[($withPrefix?$this->column_prefix:'').$column];
    }

    public function dataExists($column, $withPrefix = true)
    {
        return isset($this->data[($withPrefix?$this->column_prefix:'').$column]);
    }

    public static function __callStatic($function, $parameters)
    {
        $class = get_called_class();
        return (new $class())->{$function}(...$parameters);
    }

    public function __call($function, $parameters)
    {
        return $this->queryBuilder()->{$function}(...$parameters);
    }

    public function __set($property, $value)
    {
        $this->data[$this->column_prefix.$property] = $value;
    }

    public function __get($property)
    {
        return $this->data[$this->column_prefix.$property];
    }
}