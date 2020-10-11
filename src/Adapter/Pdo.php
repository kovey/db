<?php
/**
 * @description Pdo Client
 *
 * @package Kovey\Db
 *
 * @author kovey
 *
 * @time 2020-10-09 20:05:39
 *
 */
namespace Kovey\Db\Adapter;

use Kovey\Db\AdapterInterface;

class Pdo implements AdapterInterface
{
    /**
     * @description connection
     *
     * @var PDO
     */
    private $connection;

    /**
     * @description config
     *
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function connect(Config $config) : bool
    {
        $this->connection = new \PDO(
            sprintf('mysql:dbname=%s;host=%s;port=%s;charset=%s', $this->config->getDatabase(), $this->config->getHost(), $this->config->getPort(), $this->config->getCharset()),
            $this->config->getUser(), $this->config->getPassword(), $this->config->getOptions()
        );

        return true;
    }

	/**
	 * @description get error
	 *
	 * @return string
	 */
	public function getError() : string
	{
        return sprintf(
            'error code: %s, error msg: %s',
            $this->connection->errorCode(), implode(',', $this->connection->errorInfo())
        );
	}

    /**
     * @description query
     *
     * @param string $sql
     *
     * @return mixed
     */
    public function query(string $sql)
    {
        try {
            $result = $this->connection->query($sql);
            return $result->fetchAll();
        } catch (\PDOException $e) {
            if ($this->isDisconneted()) {
                try {
                    $this->connect();
                    $result = $this->connection->query($sql);
                    return $result->fetchAll();
                } catch (\PDOException $e) {
                    throw new DbException($e->getMessage(), $e->getCode());
                }
            }

            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

	/**
	 * @description commit transation
	 *
	 * @return bool
	 */
    public function commit() : bool
    {
        try {
            return $this->connection->commit();
        } catch (\PDOException $e) {
            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

	/**
	 * @description open transation
	 *
	 * @return bool
	 */
    public function beginTransaction() : bool
    {
        try {
            return $this->connection->beginTransaction();
        } catch (\PDOException $e) {
            if ($this->isDisconneted()) {
                try {
                    $this->connect();
                    return $this->connection->beginTransaction();
                } catch (\PDOException $e) {
                    throw new DbException($e->getMessage(), $e->getCode());
                }
            } 

            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

	/**
	 * @description cancel transation
	 *
	 * @return bool
	 */
    public function rollBack() : bool
    {
        try {
            return $this->connection->rollBack();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * @description prepare sql
     *
     * @param string $sql
     *
     * @return mixed
     */
    public function prepare(string $sql)
    {
        try {
            return $this->connection->prepare($sql);
        } catch (\PDOException $e) {
            if ($this->connection->inTransaction()) {
                throw new DbException($e->getMessage(), $e->getCode());
            }

            if ($this->isDisconneted()) {
                try {
                    $this->connect();
                    return $this->connection->prepare($sql);
                } catch (\PDOException $e) {
                    throw new DbException($e->getMessage(), $e->getCode());
                }
            } 
            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @description is disconneted
     *
     * @return bool
     */
    public function isDisconneted() : bool
    {
		return preg_match('/2006/', $this->getError()) || preg_match('/2013/', $this->getError()) || preg_match('/2002/', $this->getError());
    }

    /**
     * @description disconnet client
     *
     * @return bool
     */
    public function disconnet() : bool
    {
        $this->connection = null;
        return true;
    }

    /**
     * @description last insert id
     *
     * @return int
     */
    public function getLastInsertId() : int
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @description in transation
     *
     * @return bool
     */
    public function inTransaction() : bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * @description error info
     *
     * @return string
     */
    public function errorInfo() : string
    {
        return $this->connection->errorInfo();
    }

    /**
     * @description error code
     *
     * @return string
     */
    public function errorCode() : string
    {
        return $this->connection->errorCode();
    }

    /**
     * @description affected rows
     *
     * @param mixed $sth
     *
     * @return int
     */
    public function affectedRows($sth) : int
    {
        return $sth->rowCount();
    }

    /**
     * @description fetch row
     *
     * @param Swoole\Coroutine\MySQL\Statemen $sth
     *
     * @return Array | bool
     */
    public function fetch($sth)
    {
        return $sth->fetch();
    }
}
