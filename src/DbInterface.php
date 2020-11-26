<?php
/**
 *
 * @description database interface
 *
 * @package     Db
 *
 * @time        Tue Sep 24 09:03:29 2019
 *
 * @author      kovey
 */
namespace Kovey\Db;

use Kovey\Db\Sql\Update;
use Kovey\Db\Sql\Insert;
use Kovey\Db\Sql\Select;
use Kovey\Db\Sql\Delete;
use Kovey\Db\Sql\BatchInsert;

interface DbInterface
{
    /**
     * @description construct
     *
     * @param Array $config
     */
    public function __construct(Array $config);

    /**
     * @description connect to server
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
    public function query(string $sql) : Array;

    /**
     * @description commit transation
     *
     * @return bool
     */
    public function commit() : bool;

    /**
     * @description open transation
     *
     * @return bool
     */
    public function beginTransaction() : bool;

    /**
     * @description cancel transation
     *
     * @return bool
     */
    public function rollBack() : bool;

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
     * @throws Exception
     */
    public function fetchRow(string $table, Array $condition, Array $columns = array()) : Array | bool;

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
     * @throws Exception
     */
    public function fetchAll(string $table, Array $condition = array(), Array $columns = array()) : array;

    /**
     * @description execute update sql
     *
     * @param Update $update
     *
     * @return int
     */
    public function update(Update $update) : int;

    /**
     * @description execute insert sql
     *
     * @param Insert $insert
     *
     * @return int
     */
    public function insert(Insert $insert) : int;

    /**
     * @description execute select sql
     *
     * @param Select $select
     *
     * @param int $type
     *
     * @return Array | bool
     */
    public function select(Select $select, int $type = Select::ALL);

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
    public function batchInsert(BatchInsert $batchInsert) : int;

    /**
     * @description 删除
     *
     * @param Delete $delete
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete(Delete $delete) : int;

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
    public function transaction(callable $fun, $finally, ...$params) : bool;

    /**
     * @description exec sql
     *
     * @param string $sql
     *
     * @return int
     *
     * @throws DbException
     */
    public function exec(string $sql) : int;

    /**
     * @description is in transation
     *
     * @return bool
     */
    public function inTransaction() : bool;
}
