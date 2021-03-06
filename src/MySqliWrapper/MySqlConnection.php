<?php

namespace MySqliWrapper;

/**
 * Class used for handling a MySql connection.
 */
final class MySqlConnection
{
    /**
     * The prepared statements that are queue'd for execution.
     *
     * @var array
     */
    private $prepared_statements = [];

    /**
     * The MySqli Object.
     *
     * @var MySqli
     */
    private $sql = null;

    /**
     * The insert id from the last query.
     *
     * @var int
     */
    private $insert_id = -1;

    /**
     * The status of the connection.
     *
     * @var bool
     */
    private $is_open = false;

    /**
     * Connection host.
     *
     * @var string
     */
    private $host;

    /**
     * Connection username.
     *
     * @var string
     */
    private $username;

    /**
     * Connection password.
     *
     * @var string
     */
    private $password;

    /**
     * Connection database.
     *
     * @var string
     */
    private $database;

    /**
     * Connection port.
     *
     * @var int
     */
    private $port;

    /**
     * Connection charset.
     *
     * @var string
     */
    private $charset = 'utf8';

    /**
     * The name of this Connection in the Database connections.
     *
     * @var string
     */
    public $name = null;

    /**
     * Create a new MySqlConnection instance.
     *
     * @param array $config
     */
    public function __construct(
        $config
    ) {
        $this->host = $config['host'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->database = $config['database'];
        $this->port = $config['port'];
        $this->charset = $config['charset'];
        $this->name = $config['name'];
    }

    /**
     * Close the Sql Connection and destroy the object.
     */
    public function __destruct()
    {
        $this->closeSQL();
    }

    /**
     * Executes a Query.
     *
     * @param \MySqliWrapper\Query $query
     * @param bool                 $return
     *
     * @return ResultSet
     * @throws \Exception
     */
    public function execute($query, $return = false)
    {
        return $this->executeSql($query->getRawQuery(), $query->getBinds(), $return);
    }

    /**
     * Executes a raw query.
     *
     * @param string  $query
     * @param array   $binds
     * @param bool $return
     *
     * @return ResultSet
     * @throws \Exception
     */
    public function executeSql($query, $binds = [], $return = false)
    {
        if (! $this->is_open) {
            if (! $this->openSql()) {
                throw new \Exception('Unable to open SQL Connection.');
            }
        }
        $id = $this->prepareStatement(count($this->prepared_statements), $query);
        $ret = null;
        if ($id !== null) {
            $ret = $this->executePreparedStatement($id, $binds, $return);
        }

        return $ret;
    }

    /**
     * Retrieve the last insert ID.
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->insert_id;
    }

    /**
     * Escapes a string for sql injection.
     *
     * @param string $str
     *
     * @return string
     */
    public function escapeString($str)
    {
        if (! $this->is_open) {
            if (! $this->openSql()) {
                throw new \Exception('Failed to open database connection.');
            }
        }

        return $this->sql->real_escape_string($str);
    }

    /**
     * Closes the Sql connection.
     */
    private function closeSql()
    {
        if ($this->sql != null && $this->is_open) {
            $this->sql->close();
            $this->sql = null;
        }
    }

    /**
     * Open the Sql connection.
     */
    private function openSql()
    {
        $ret = false;
        $this->closeSQL();
        $this->sql = @new \mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database,
            $this->port
        );

        if ($this->sql->connect_errno) {
            $this->sql = null;

            return false;
        }

        $this->sql->set_charset($this->charset);

        if ($this->sql->connect_errno) {
            $this->sql = null;

            return false;
        }

        $this->is_open = true;

        return true;
    }

    /**
     * Executes prepared statement.
     *
     * @param string  $prepareTitle
     * @param bool $return
     *
     * @return bool|array|null
     */
    private function executePreparedStatement($prepareTitle, $binds = [], $return = false)
    {
        $ret = null;
        $statement = $this->getPreparedStatement($prepareTitle);
        $tmp = [];
        foreach ($binds as $key => $value) {
            $tmp[$key] = &$binds[$key];
        }
        if (count($binds) > 0) {
            call_user_func_array([$statement, 'bind_param'], $tmp);
            if ($this->sql->errno) {
                throw new \Exception(
                    'Error while applying binds to SQL Prepared Statement: '.
                    $this->sql->error
                );
            }
        }
        $ret = $statement->execute();
        if ($statement->errno) {
            throw new \Exception(
                'Error while executing Prepared Statement: '.
                $statement->error
            );
        }
        $this->insert_id = $statement->insert_id;
        if ($return) {
            $ret = $this->stmt_get_result($statement);
            if ($statement->errno) {
                throw new \Exception(
                    'Error while retreiving result set from MySQL Prepared Statement: '.
                    $statement->error
                );
            }
        }
        $this->removePreparedStatement($prepareTitle);

        return $ret;
    }

    /**
     * This function is used instead of the mysqlnd driver function.
     *
     * @param  mysqli_stmt $statement
     * @return array
     */
    public function stmt_get_result($statement)
    {
        $result = [];
        $statement->store_result();
        for ($i = 0; $i < $statement->num_rows; $i++) {
            $metadata = $statement->result_metadata();
            $params = [];
            while ($field = $metadata->fetch_field()) {
                $params[] = &$result[$i][$field->name];
            }
            call_user_func_array([$statement, 'bind_result'], $params);
            $statement->fetch();
        }

        return $result;
    }

    /**
     * Create new prepared statement in the system.
     *
     * @param string $prepareTitle
     * @param string $prepareBody
     *
     * @return string
     */
    private function prepareStatement($prepareTitle, $prepareBody)
    {
        $ret = null;
        $this->prepared_statements[$prepareTitle] = $this->sql->prepare($prepareBody);
        if ($this->sql->errno) {
            throw new \Exception('Error while Preparing SQL: '.$this->sql->error);
        } else {
            $ret = $prepareTitle;
        }

        return $ret;
    }

    /**
     * Removes a Prepared Statement from the system.
     *
     * @param string $prepareTitle
     */
    private function removePreparedStatement($prepareTitle)
    {
        $this->prepared_statements[$prepareTitle]->close();
        unset($this->prepared_statements[$prepareTitle]);
    }

    /**
     * Retrieves a prepared statement from the storage array.
     *
     * @param string $prepareTitle
     *
     * @return PreparedStatement
     */
    private function getPreparedStatement($prepareTitle)
    {
        return $this->prepared_statements[$prepareTitle];
    }

    /**
     * Hide debug output to mask sensitive variables.
     *
     * @return array
     */
    public function __debugInfo()
    {
        $result = get_object_vars($this);
        unset($result['username']);
        unset($result['password']);
        unset($result['database']);
        unset($result['host']);
        unset($result['port']);

        return $result;
    }

    /**
     * Retrieve an instance of a Model based on a table.
     *
     * @return \MySqliWrapper\Model
     */
    public function model($table, $config = [])
    {
        return new \MySqliWrapper\Model($this->name, $table, $config);
    }

    /**
     * Retrieve a Query builder object for the specified table.
     *
     * @return \MySqliWrapper\QueryBuilder
     */
    public function table($table, $config = [])
    {
        return new \MySqliWrapper\QueryBuilder($this->name, $table, $config);
    }
}
