<?php
/**
 * @description database adapter interface
 *
 * @package Kovey\Db
 *
 * @author Kovey
 *
 * @time 2020-10-09 19:39:40
 *
 */
namespace Kovey\Db;

use Kovey\Db\Adapter\Config;

Interface AdapterInterface
{
    /**
     * @description construct
     *
     * @param Config $config
     */
    public function __construct(Config $config);

    /**
     * @description connect to server
     *
     * @param Config $config
     *
     * @return bool
     */
    public function connect() : bool;

    /**
     * @description get error
     *
     * @return string
     */
    public function getError() : string;

    /**
     * @description query
     *
     * @param string $sql
     *
     * @return mixed
     */
    public function query(string $sql);

	/**
	 * @description commit transation
	 *
	 * @return null
	 */
	public function commit();

	/**
	 * @description open transation
	 *
	 * @return bool
	 */
	public function beginTransaction() : bool;

	/**
	 * @description cancel transation
	 *
	 * @return null
	 */
	public function rollBack();

    /**
     * @description prepare sql
     *
     * @param string $sql
     *
     * @return mixed
     */
    public function prepare(string $sql);

    /**
     * @description is disconneted
     *
     * @return bool
     */
    public function isDisconneted() : bool;

    /**
     * @description disconnet client
     *
     * @return bool
     */
    public function disconnet() : bool;

    /**
     * @description last insert id
     *
     * @return int
     */
    public function getLastInsertId() : int;

    /**
     * @description in transation
     *
     * @return bool
     */
    public function inTransaction() : bool;

    /**
     * @description error info
     *
     * @return string
     */
    public function errorInfo() : string;

    /**
     * @description error code
     *
     * @return string
     */
    public function errorCode() : string;

    /**
     * @description affected rows
     *
     * @param mixed $sth
     *
     * @return int
     */
    public function affectedRows($sth) : int;

    /**
     * @description fetch row
     *
     * @param Swoole\Coroutine\MySQL\Statemen $sth
     *
     * @return Array | bool
     */
    public function fetch($sth) : Array | bool;

    /**
     * @description execute sql
     *
     * @param string $sql
     *
     * @return int
     */
    public function exec($sql) : int;
}
