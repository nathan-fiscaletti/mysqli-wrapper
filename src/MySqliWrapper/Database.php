<?php

namespace MySqliWrapper;

final class Database
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
     * @param array $connection
     */
    public static function register($config)
    {
        $connection = new \MySqliWrapper\MySqlConnection($config);
        self::$connections[$config['name']] = $connection;
    }

    /**
     * Retrieve a connection based on it's name.
     *
     * @param string $name
     *
     * @return \MySqliWrapper\MySqlConnection
     */
    public static function get($name)
    {
        if (! isset(self::$connections[$name])) {
            return;
        }

        return self::$connections[$name];
    }

    /**
     * Retrieve the first connection.
     *
     * @return \MySqliWrapper\MySqlConnection
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
