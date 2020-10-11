<?php
/**
 * @description Swoole Mysql
 *
 * @package Kovey\Db
 *
 * @author kovey
 *
 * @time 2020-10-09 19:52:06
 *
 */
namespace Kovey\Db\Adapter;

use Swoole\Coroutine\MySQL;
use Kovey\Db\AdapterInterface;
use Kovey\Db\Exception\DbException;

class Swoole implements AdapterInterface
{
    /**
     * @description connection
     *
     * @var MySQL
     */
    private $connection;

    /**
     * @description config
     *
     * @var Config
     */
    private $config;

    /**
     * @description is in transation
     *
     * @var bool
     */
    private $isInTransaction = false;

    public function __construct(Config $config)
    {
        $this->connection = new MySQL();
        $this->config = $config;
    }

    public function connect() : bool
    {
        $this->isInTransaction = false;
        return $this->connection->connect(array(
			'host' => $this->config->getHost(),
			'port' => $this->config->getPort(),
			'user' => $this->config->getUser(),
			'password' => $this->config->getPassword(),
			'database' => $this->config->getDatabase(),
			'charset' => $this->config->getCharset(),
			'fetch_mode' => true
        ));
    }

	/**
	 * @description get error
	 *
	 * @return string
	 */
	public function getError() : string
	{
        return sprintf(
            'error code: %s, error msg: %s, connect error code: %s, connect error msg: %s',
            $this->connection->errno, $this->connection->error, $this->connection->connect_errno, $this->connection->connect_error
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
		if (!$this->connection->connected) {
            if (!$this->connect()) {
                throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
            }
		}

        $result = $this->connection->query($sql);
		if (!$result) {
			if ($this->isDisconneted()) {
                if (!$this->connect()) {
                    throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
                }
			}

			$result = $this->connection->query($sql);
		}

        if (!$result) {
            throw new DbException($this->connection->error, $this->connection->errno);
        }

	    return $this->connection->fetchAll();
    }

	/**
	 * @description commit transation
	 *
	 * @return bool
	 */
    public function commit()
    {
        $result = $this->connection->commit();
        if (!$result) {
            throw new DbException($this->connection->error, $this->connection->errno);
        }

        $this->isInTransaction = false;
        return $result;
    }

	/**
	 * @description open transation
	 *
	 * @return bool
	 */
    public function beginTransaction() : bool
    {
		if (!$this->connection->connected) {
            if (!$this->connect()) {
                throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
            }
		}

		if (!$this->connection->begin()) {
			if ($this->isDisconneted()) {
                if (!$this->connect()) {
                    throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
                }
                if (!$this->connection->begin()) {
                    throw new DbException($this->connection->error, $this->connection->errno);
                }

                $this->isInTransaction = true;
                return true;
			}

            throw new DbException($this->connection->error, $this->connection->errno);
		}

        $this->isInTransaction = true;
		return true;
    }

	/**
	 * @description roll back transation
	 *
	 * @return bool
	 */
    public function rollBack() : bool
    {
        $result = $this->connection->rollback();
        $this->isInTransaction = false;
        return $result;
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
		if (!$this->connection->connected) {
            if ($this->isInTransaction) {
                throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
            }

            if (!$this->connect()) {
                throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
            }
		}

        $sth = $this->connection->prepare($sql);
		if (!$sth) {
            if ($this->isInTransaction) {
                throw new DbException($this->connection->error, $this->connection->errno);
            }
			if ($this->isDisconneted()) {
                if (!$this->connect()) {
                    throw new DbException($this->connection->connect_error, $this->connection->connect_errno);
                }

				$sth = $this->connection->prepare($sql);
				if (!$sth) {
                    throw new DbException($this->connection->error, $this->connection->errno);
				}
			} else {
                throw new DbException($this->connection->error, $this->connection->errno);
			}
		}

        return $sth;
    }

    /**
     * @description is disconneted
     *
     * @return bool
     */
    public function isDisconneted() : bool
    {
		return !$this->connection->connected || preg_match('/2006/', $this->getError()) || preg_match('/2013/', $this->getError()) || preg_match('/2002/', $this->getError());
    }

    /**
     * @description disconnet client
     *
     * @return bool
     */
    public function disconnet() : bool
    {
        $this->connection->close();
    }

    /**
     * @description last insert id
     *
     * @return int
     */
    public function getLastInsertId() : int
    {
        return $this->connection->insert_id;
    }

    /**
     * @description in transation
     *
     * @return bool
     */
    public function inTransaction() : bool
    {
        return $this->isInTransaction;
    }

    /**
     * @description error info
     *
     * @return string
     */
    public function errorInfo() : string
    {
        return $this->connection->error;
    }

    /**
     * @description error code
     *
     * @return string
     */
    public function errorCode() : string
    {
        return strval($this->connection->errno);
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
        return $this->connection->affected_rows;
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
        $row = false;
        while ($ret = $sth->fetch()) {
            $row = $ret;
        }

        return $row;
    }
}
