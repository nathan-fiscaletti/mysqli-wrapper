<?php

namespace MySqliWrapper;

/**
 * Class used for handling a MySql connection.
 */
final class SqlConnection
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
     * Create a new SqlConnection instance.
     *
     * @param string  $host
     * @param string  $username
     * @param string  $password
     * @param string  $database
     * @param int $port
     */
    public function __construct(
        $host = null,
        $username = null,
        $password = null,
        $database = null,
        $port = null
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
    }

    /**
     * Close the Sql Connection and destroy the object.
     */
    public function __destruct()
    {
        $this->closeSQL();
    }

    /**
     * Executes a query in sql.
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
                throw new \Exception("Unable to open SQL Connection.");
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
     * Escapes a string for sql injection.
     *
     * @param string $str
     */
    public function escapeString($str)
    {
        if (! $this->is_open) {
            if (! $this->openSql()) {
                trigger_error(
                    'Failed one or more custom databases. Please check SqlServers.php.',
                    E_USER_WARNING
                );
                exit();
            }
        }

        return $this->sql->real_escape_string(strip_tags($str));
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
        $this->sql = @new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database,
            $this->port
        );
        if ($this->sql->connect_errno) {
            $this->sql = null;
        } else {
            $this->is_open = true;
            $ret = true;
        }
        return $ret;
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
                trigger_error(
                    'Error while applying binds to SQL Prepared Statement: '.
                    $this->sql->error,
                    E_USER_WARNING
                );
            }
        }
        $ret = $statement->execute();
        if ($statement->errno) {
            trigger_error('Error while executing Prepared Statement: '.$statement->error, E_USER_WARNING);
        }
        $this->insert_id = $statement->insert_id;
        if ($return) {
            $ret = $this->stmt_get_result($statement);
            if ($statement->errno) {
                trigger_error(
                    'Error while retreiving result set from MySQL Prepared Statement: '.
                    $statement->error,
                    E_USER_WARNING
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
    function stmt_get_result( $statement ) {
        $result = array();
        $statement->store_result();
        for ( $i = 0; $i < $statement->num_rows; $i++ ) {
            $metadata = $statement->result_metadata();
            $params = array();
            while ( $field = $metadata->fetch_field() ) {
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
            trigger_error('Error while Preparing SQL: '.$this->sql->error, E_USER_WARNING);
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
}
