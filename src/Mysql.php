<?php
/**
 *
 * @description Mysql Client 
 *
 * @package     Kovey\Db
 *
 * @time        Tue Sep 24 09:02:49 2019
 *
 * @author      kovey
 */
namespace Kovey\Db;

use Kovey\Db\Sql\Update;
use Kovey\Db\Sql\Insert;
use Kovey\Db\Sql\Select;
use Kovey\Db\Sql\BatchInsert;
use Kovey\Db\Sql\Delete;
use Kovey\Db\Sql\Where;
use Kovey\Db\Adapter\Config;
use Kovey\Db\AdapterInterface;
use Kovey\Db\Exception\DbException;
use Kovey\Logger\Db as DbLogger;
use Swoole\Coroutine\MySQL\Statement;

class Mysql implements DbInterface
{
    /**
     * @description database adapter
     *
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @description is dev
     *
     * @var bool
     */
    private $isDev = false;

    /**
     * @description construct
     *
     * @param Array $config
     */
    public function __construct(Array $config)
    {
        $dev = $config['dev'] ?? 'Off';
        $this->isDev = $dev === 'On';

        $this->adapter = Adapter::factory($config['adapter'] ?? Adapter::DB_ADAPTER_PDO, new Config(array(
            'database' => $config['dbname'],
            'host' => $config['host'],
            'port' => $config['port'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => $config['charset'],
            'options' => $config['options'] ?? array()
        )));

        if (!$this->adapter instanceof AdapterInterface) {
            throw new DbException('adapter is not implements AdapterInterface', 1002);
        }
    }

    /**
     * @description connect to server
     *
     * @return bool
     */
    public function connect() : bool
    {
        return $this->adapter->connect();
    }

    /**
     * @description get error
     *
     * @return string
     */
    public function getError() : string
    {
        return $this->adapter->getError();
    }

    /**
     * @description query
     *
     * @param string $sql
     *
     * @return mixed
     *
     * @throws DbException"
     */
    public function query(string $sql) : Array
    {
        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }
        try {
            $result = $this->adapter->query($sql);
        } catch (DbException $e) {
            throw $e;
        } finally {
            if ($this->isDev) {
                DbLogger::write($sql, microtime(true) - $begin);
            }
        }

        return $result;
    }

    /**
     * @description commit transation
     *
     * @return bool
     */
    public function commit() : bool
    {
        return $this->adapter->commit();
    }

    /**
     * @description begin transation
     *
     * @return bool
     *
     * @throws DbException
     */
    public function beginTransaction() : bool
    {
        return $this->adapter->beginTransaction();
    }

    /**
     * @description rollback transation
     *
     * @return bool
     */
    public function rollBack() : bool
    {
        return $this->adapter->rollback();
    }

    /**
     * @description fetch row
     *
     * @param string $table
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @return Array | bool
     *
     * @throws DbException
     */
    public function fetchRow(string $table, Array | Where $condition, Array $columns = array()) : Array | bool
    {
        $select = new Select($table);
        $select->columns($columns);
        if ($condition instanceof Where) {
            $select->where($condition);
            return $this->select($select, $select::SINGLE);
        }

        if (count($condition) > 0) {
            $where = new Where();
            foreach ($condition as $key => $val) {
                if (is_numeric($key)) {
                    $where->statement($val);
                    continue;
                }

                if (is_array($val)) {
                    $where->in($key, $val);
                    continue;
                }

                $where->eq($key, $val);
            }

            $select->where($where);
        }

        return $this->select($select, $select::SINGLE);
    }

    /**
     * @description fetch all rows
     *
     * @param string $table
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @return Array
     *
     * @throws DbException
     */
    public function fetchAll(string $table, Array | Where $condition = array(), Array $columns = array()) : array
    {
        $select = new Select($table);
        $select->columns($columns);
        if ($condition instanceof Where) {
            $select->where($condition);
            $rows = $this->select($select);
            if ($rows === false) {
                return array();
            }

            return $rows;
        }

        if (count($condition) > 0) {
            $where = new Where();
            foreach ($condition as $key => $val) {
                if (is_numeric($key)) {
                    $where->statement($val);
                    continue;
                }

                if (is_array($val)) {
                    $where->in($key, $val);
                    continue;
                }

                $where->eq($key, $val);
            }

            $select->where($where);
        }
        
        $rows = $this->select($select);
        if ($rows === false) {
            return array();
        }

        return $rows;
    }

    /**
     * @description sql update
     *
     * @param Update $update
     *
     * @return int
     */
    public function update(Update $update) : int
    {
        $sth = $this->prepare($update);
        $affected = $this->adapter->affectedRows($sth);
        if ($affected < 1) {
            throw new DbException('update sql affected rows is ' . $affected, 1001);
        }

        return $affected;
    }

    /**
     * @description sql insert
     *
     * @param Insert $insert
     *
     * @return int
     */
    public function insert(Insert $insert) : int
    {
        $sth = $this->prepare($insert);
        $affected = $this->adapter->affectedRows($sth);
        if ($affected < 1) {
            throw new DbException('insert sql affected rows is ' . $affected, 1001);
        }

        return $this->adapter->getLastInsertId();
    }

    /**
     * @description prepare sql
     *
     * @param SqlInterface $sqlObj
     *
     * @return Statement | \PDOStatement
     */
    private function prepare(SqlInterface $sqlObj) : Statement | \PDOStatement
    {
        $sql = $sqlObj->getPrepareSql();
        if ($sql === false) {
            throw new DbException('sql is empty', 1000);
        }

        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }

        try {
            $sth = $this->adapter->prepare($sql);
            if (!$sth->execute($sqlObj->getBindData())) {
                $sth = $this->adapter->prepare($sql);
                if (!$sth->execute($sqlObj->getBindData())) {
                    throw new DbException($this->adapter->errorInfo(), $this->adapter->errorCode());
                }
            }
            return $sth;
        } catch (\PDOException $e) {
            $this->adapter->parseError($e);
            if ($this->adapter->inTransaction()) {
                throw new DbException($e->getMessage(), $e->getCode());
            }
            if ($this->adapter->isDisconneted()) {
                if (!$this->adapter->connect()) {
                    throw new DbException($this->adapter->getError(), 1013);
                }
                try {
                    $sth = $this->adapter->prepare($sql);
                    $sth->execute($sqlObj->getBindData());
                    return $sth;
                } catch (\PDOException $e) {
                    throw new DbException($e->getMessage(), $e->getCode());
                }
            }
            throw new DbException($e->getMessage(), $e->getCode());
        } catch (DbException $e) {
            throw $e;
        } finally {
            if ($this->isDev) {
                DbLogger::write($sqlObj->toString(), microtime(true) - $begin);
            }
        }
    }

    /**
     * @description sql select
     *
     * @param Select $select
     *
     * @param int $type
     *
     * @return Array | bool
     */
    public function select(Select $select, $type = Select::ALL) : bool | Array
    {
        $sth = $this->prepare($select);

        if ($type == Select::SINGLE) {
            return $this->adapter->fetch($sth);
        }
        
        return $sth->fetchAll();
    }

    /**
     * @description close connect
     *
     * @return null
     */
    public function __destruct()
    {
        try {
            $this->adapter->disconnet();
        } catch (\Throwable $e) {
        }
    }

    /**
     * @description batch insert
     *
     * @param BatchInsert $batchInsert
     *
     * @return int
     *
     * @throws DbException
     *
     */
    public function batchInsert(BatchInsert $batchInsert) : int
    {
        $sth = $this->prepare($batchInsert);
        $affected = $this->adapter->affectedRows($sth);
        if ($affected < 1) {
            throw new DbException('batch insert sql affected rows is ' . $affected, 1001);
        }

        return $affected;
    }

    /**
     * @description delete
     *
     * @param Delete $delete
     *
     * @return int
     *
     * @throws DbException
     */
    public function delete(Delete $delete) : int
    {
        $sth = $this->prepare($delete);
        $affected = $this->adapter->affectedRows($sth);
        if ($affected < 1) {
            throw new DbException('batch insert sql affected rows is ' . $affected, 1001);
        }

        return $affected;
    }

    /**
     * @description run transation
     *
     * @param callable $fun
     *
     * @param mixed $finally
     *
     * @param ...$params
     *
     * @return bool
     *
     * @throws DbException
     */
    public function transaction(callable $fun, mixed $finally, mixed ...$params) : bool
    {
        if (!$this->beginTransaction()) {
            return false;
        }

        try {
            call_user_func($fun, $this, ...$params);
            $this->commit();
        } catch (DbException $e) {
            $this->rollBack();
            throw $e;
        } finally {
            if (is_callable($finally)) {
                call_user_func($finally, $this, ...$params);
            }
        }

        return true;
    }

    /**
     * @description exec sql
     *
     * @param string $sql
     *
     * @return int
     */
    public function exec(string $sql) : int
    {
        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }
        try {
            return $this->adapter->exec($sql);
        } catch (DbException $e) {
            throw $e;
        } finally {
            if ($this->isDev) {
                DbLogger::write($sql, microtime(true) - $begin);
            }
        }
    }
    
    /**
     * @description is in transation
     *
     * @return bool
     */
    public function inTransaction() : bool
    {
        return $this->adapter->inTransaction();
    }

    /**
     * @description get last insert id
     *
     * @return int
     */
    public function getLastInsertId() : int
    {
        return $this->adapter->getLastInsertId();
    }
}
