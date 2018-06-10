<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Database;

use PDO;

class Connector {

    /**
     * 
     * @var PDO array
     */
    protected $connections = array();
    
    /**
     *
     * Array of all instances of CODOF\Database\Drivers\Query
     * @var array
     */
    protected $instances = array();

    /**
     * Current connection name in use
     * @var string 
     */
    protected $connectionInUse = null;
    
    /**
     * Connection name to the codoforum installed database
     * @var string 
     */
    private $defaultConnectionName = 'default';

    /**
     * Creates a connection to database
     * 
     * @param array $config database configuration
     */
    protected function createConnection($config) {

        try {

            $connection = new \PDO($config['DSN'], $config['user'], $config['pass'], array(
                \PDO::ATTR_PERSISTENT => $config['persistent']
            ));
        } catch (PDOException $e) {

            die($e->getMessage());
        }

        $connection->exec("SET CHARACTER SET utf8");
        $connection->exec("SET NAMES utf8");
        $connection->exec("SET SESSION sql_mode = 'ANSI';");

        return $connection;
    }

    /**
     * Adds a PDO connection
     * @param string $name
     * @param array $config
     * @return CODOF\Database\Drivers\Query
     */
    public function addConnection($name, $config) {

        if (!isset($this->connections[$name])) {

            $this->connections[$name] = $this->createConnection($config);
        }

        $this->connectionInUse = $name;

        $this->instances[$name] = $this->getQueryInstance($this->connections[$name], $config);

        return $this->instances[$name];
    }

    /**
     * 
     * Gets CODOF\Database\Drivers\Query instance for specified connection
     * @param type $name
     */
    public function getConnection($name) {

        if (isset($this->instances[$name])) {

            return $this->instances[$name];
        }
    }

    /**
     * Set which connection to use 
     * @param string $name
     */
    public function setConnection($name) {

        $this->connectionInUse = $name;
    }
    
    public function disconnect($name = null) {
        
        $name = $name ?: $this->defaultConnectionName;
        
        unset($this->connections[$name]);
        unset($this->instances[$name]);
    }

    /**
     * Creates a default connection to codoforum installed database
     */
    protected function createDefaultConnection() {

        //get default DB configuration
        $config = \get_codo_db_conf();
        $name = $this->defaultConnectionName;

        $this->connections[$name] = $this->createConnection($config);
        $this->instances[$name] = $this->getQueryInstance($this->connections[$name], $config);
    }

    /**
     * 
     * Gets a database connection
     * @param bool $force
     * @return CODOF\Database\Drivers\Query
     */
    protected function getCurrentConnection() {

        //always create default connection to codoforum database
        if (!isset($this->connections[$this->defaultConnectionName])) {

            $this->createDefaultConnection();
        }

        if (!isset($this->connectionInUse)) {

            $this->connectionInUse = $this->defaultConnectionName;
        }

        return $this->instances[$this->connectionInUse];
    }

    
    /**
     * 
     * @param PDO $pdo
     * @param type $config
     * @return \CODOF\Database\Drivers\MysqlQuery
     */
    protected function getQueryInstance(PDO $pdo, $config) {

        $dsn = $config['DSN'];
        $driver = stristr($dsn, ":", true);

        if ($driver == 'mysql') {

            return new Drivers\MysqlQuery($pdo, $this->connectionInUse);
        }

        echo "Driver $driver not implemented";
    }

    /**
     * 
     * Dynamically call query methods
     * @param type $method
     * @param type $parameters
     * @return type
     */
    public function __call($method, $parameters) {

        return call_user_func_array(array($this->getCurrentConnection(), $method), $parameters);
    }

}
