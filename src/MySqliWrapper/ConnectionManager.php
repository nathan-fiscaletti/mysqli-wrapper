<?php

namespace MySqliWrapper;

class ConnectionManager
{
    private static $connections = [];

    public static function register($name, $connection)
    {
        if (! ($connection instanceof \MySqliWrapper\SqlConnection)) {
            throw new \Exception('Type is not \MySqliWrapper\SqlConnection');
        }
        $connection->name = $name;
        self::$connections[$name] = $connection;
    }

    public static function get($name)
    {
        if (! isset(self::$connections[$name])) {
            return null;
        }

        return self::$connections[$name];
    }

    public static function first()
    {
        return self::$connections[
            array_keys(self::$connections)[0]
        ];
    }

    public static function table($name, $config = [])
    {
        return self::$connections[
            array_keys(self::$connections)[0]
        ]->table($name, $config);
    }

    public static function model($name, $config = [])
    {
        return self::$connections[
            array_keys(self::$connections)[0]
        ]->model($name, $config);
    }
}