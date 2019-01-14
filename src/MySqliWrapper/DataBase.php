<?php

namespace MySqliWrapper;

final class DataBase
{
    /**
     * The connections currently active.
     * 
     * @var array
     */
    private static $connections = [];

    /**
     * Register a connection.
     * 
     * @param string                       $name
     * @param \MySqliWrapper\SqlConnection $connection
     */
    public static function register($name, $connection)
    {
        if (! ($connection instanceof \MySqliWrapper\SqlConnection)) {
            throw new \Exception('Type is not \MySqliWrapper\SqlConnection');
        }
        $connection->name = $name;
        self::$connections[$name] = $connection;
    }

    /**
     * Retrieve a connection based on it's name.
     * 
     * @param string $name
     * 
     * @return \MySqliWrapper\SqlConnection
     */
    public static function get($name)
    {
        if (! isset(self::$connections[$name])) {
            return null;
        }

        return self::$connections[$name];
    }

    /**
     * Retrieve the first connection.
     * 
     * @return \MySqliWrapper\SqlConnection
     */
    public static function first()
    {
        return self::$connections[
            array_keys(self::$connections)[0]
        ];
    }

    /**
     * Create a query builder using the first connection.
     * 
     * @param string $table
     * @param array  $config
     * 
     * @return \MySqliWrapper\QueryBuilder
     */
    public static function table($table, $config = [])
    {
        return self::first()->table($table, $config);
    }

    /**
     * Retrieve a Model using the first connection.
     * 
     * @param string $table
     * @param array  $config
     * 
     * @return \MySqliWrapper\Model
     */
    public static function model($table, $config = [])
    {
        return self::first()->model($table, $config);
    }
}