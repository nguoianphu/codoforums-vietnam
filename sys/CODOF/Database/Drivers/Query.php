<?php

/*
 * @CODOLICENSE
 */

namespace CODOF\Database\Drivers;

use PDO;
use Closure;

class Query {

    /**
     * PDO connection
     * @var PDO 
     */
    protected $pdo;

    /**
     *
     * @var string 
     */
    protected $query;

    /**
     *
     * @var PDOStatement 
     */
    protected $currentPDOStatement;

    /**
     * Current Transaction depth for nested transactions
     * @var int 
     */
    protected $transactionDepth = 0;

    /**
     * Database drivers that currently support transaction nesting
     * @var array 
     */
    protected $supportedDrivers = array('pgsql', 'mysql', 'mysqli', 'sqlite');
    
    protected $connectionName;
    
    protected $bindings = false;

    public function __construct(PDO $pdo, $name) {

        $this->pdo = $pdo;
        $this->connectionName = $name;
        
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function query($query, $bindings = false) {

        $this->query = $query;

        if ($bindings) {

            $this->bindings = (is_array($bindings)) ? $bindings : array($bindings);
        }

        return $this;
    }

    public function exec() {

        return $this->buildQuery();
    }

    /**
     * Gets the last inserted id
     * @return int
     */
    public function lastInsertId() {

        return $this->pdo->lastInsertId();
    }

    /**
     * Execute the query statement
     */
    protected function buildQuery() {

        if ($this->bindings) {

            $this->currentPDOStatement = $this->pdo->prepare($this->query);
            $status = $this->currentPDOStatement->execute($this->bindings);            
            
        } else {

            $status = $this->currentPDOStatement = $this->pdo->query($this->query);
        }
        
        return $status;
    }

    public function transaction(Closure $closure) {

        $this->beginTransaction();

        //if any error catch it and roll back else commit
        try {

            $res = $closure($this);
            $this->commit();
        } catch (Exception $ex) {

            $this->rollBack();
            throw $ex;
        }

        return $res;
    }

    /**
     * Start a new PDO transaction
     */
    public function beginTransaction() {

        if ($this->transactionDepth === 0 || !$this->isTransactionNestable()) {

            $this->pdo->beginTransaction();
        } else {

            //is inside transaction and nesting is supported
            $this->pdo->exec("SAVEPOINT LEVEL{$this->transactionDepth}");
        }

        $this->transactionDepth++;
    }

    /**
     * Commits PDO transaction
     */
    public function commit() {

        $this->transactionDepth--;

        if ($this->transactionDepth == 0 || !$this->isTransactionNestable()) {

            $this->pdo->commit();
        } else {

            //is inside transaction
            $this->pdo->exec("RELEASE SAVEPOINT LEVEL{$this->transactionDepth}");
        }
    }

    /**
     * Rolls back a failed transaction
     */
    public function rollBack() {

        $this->transactionDepth--;

        if ($this->transactionDepth == 0 || !$this->isTransactionNestable()) {

            $this->pdo->rollBack();
        } else {
            $this->pdo->exec("ROLLBACK TO SAVEPOINT LEVEL{$this->transactionDepth}");
        }
    }

    /**
     * Checks if current database driver supports transaction nesting
     * @return bool
     */
    protected function isTransactionNestable() {

        return in_array($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME), $this->supportedDrivers);
    }

    /**
     * 
     * @return PDO
     */
    public function getPDO() {

        return $this->pdo;
    }

    /*
      public static function is_field_present($value, $field) {

      //no need for limit because the fields are always checked for uniqueness
      $qry = "SELECT id FROM codo_users WHERE $field=:value";
      $obj = self::$connection->prepare($qry);
      $obj->execute(array("value" => $value));

      if($obj->rowCount()) {

      $res = $obj->fetch();
      return $res['id'];
      }

      return false;
      }
     */

    /**
     * 
     * Directly use PDOStatement to fetch the results . 
     * We do not have to implement each and every PDO fetch method using
     * this magical method
     * @param type $method
     * @param type $parameters
     * @return type
     */
    public function __call($method, $parameters) {

        $this->buildQuery($this->query);

        return call_user_func_array(array($this->currentPDOStatement, $method), $parameters);
    }

}
