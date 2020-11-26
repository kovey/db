<?php
/**
 *
 * @description modal base
 *
 * @package    Kovey\Db\Model 
 *
 * @time        2020-01-19 17:55:12
 *
 * @author      kovey
 */
namespace Kovey\Db\Model;

use Kovey\Db\Sql\Select;
use Kovey\Db\Sql\Where;
use Kovey\Db\Sql\Insert;
use Kovey\Db\Sql\Update;
use Kovey\Db\Sql\Delete;
use Kovey\Db\Sql\BatchInsert;
use Kovey\Db\Exception\DbException;

abstract class Base
{
    /**
     * @description table name
     *
     * @var string
     */
    protected string $tableName;

    /**
     * @description insert data
     *
     * @param Array $data
     *
     * @throws DbException
     */
    public function insert(Array $data) : int
    {
        $insert = new Insert($this->tableName);
        foreach ($data as $key => $val) {
            $insert->$key = $val;
        }

        return $this->database->insert($insert);
    }

    /**
     * @description update
     *
     * @param Array $data
     *
     * @param Array $condition
     *
     * @throws DbException
     */
    public function update(Array $data, Array $condition) : int
    {
        $update = new Update($this->tableName);
        foreach ($data as $key => $val) {
            $update->$key = $val;
        }

        $update->where($condition);
        return $this->database->update($update);
    }

    /**
     * @description fetch row
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @return Array
     *
     * @throws DbException
     */
    public function fetchRow(Array $condition, Array $columns) : Array | bool
    {
        if (empty($columns)) {
            throw new DbException('selected columns is empty.', 1004); 
        }

        return $this->database->fetchRow($this->tableName, $condition, $columns);
    }

    /**
     * @description fetch all rows
     *
     * @param Array $condition
     *
     * @param Array  $columns
     *
     * @return Array
     *
     * @throws DbException
     */
    public function fetchAll(Array $condition, Array $columns) : Array
    {
        if (empty($columns)) {
            throw new DbException('selected columns is empty.', 1005); 
        }

        return $this->database->fetchAll($this->tableName, $condition, $columns);
    }

    /**
     * @description batch insert
     *
     * @param Array $rows
     *
     * @return int
     *
     * @throws DbException
     */
    public function batchInsert(Array $rows) : int
    {
        if (empty($rows)) {
            throw new DbException('rows is empty.', 1006);
        }

        $batchInsert = new BatchInsert($this->tableName);
        foreach ($rows as $row) {
            $insert = new Insert($this->tableName);
            foreach ($row as $key => $val) {
                $insert->$key = $val;
            }

            $batchInsert->add($insert);
        }

        return $this->database->batchInsert($batchInsert);
    }

    /**
     * @description delete
     *
     * @param Array $data
     *
     * @param Array $condition
     *
     * @return int
     *
     * @throws DbException
     */
    public function delete(Array $condition) : int
    {
        $delete = new Delete($this->tableName);
        $delete->where($condition);
        return $this->database->delete($delete);
    }

    /**
     * @description fetch rows by page
     *
     * @param Array $condition
     *
     * @param Array $columns
     *
     * @param int $page
     *
     * @param int $pageSize
     *
     * @param DbInterface $db
     *
     * @param string $tableAs
     *
     * @param string $order
     *
     * @param string $group
     *
     * @param Array $join
     *
     * @return Array | bool
     *
     * @throws DbException
     */
    public function fetchByPage(Array $condition, Array $columns, int $page, int $pageSize, string $tableAs = '', string $order = '', string $group = '', Array $join = array())
    {
        $offset = intval(($page - 1) * $pageSize);
        $select = new Select($this->tableName, $tableAs);
        $totalSelect = new Select($this->tableName, $tableAs);
        $select->columns($columns)
               ->limit($pageSize)
               ->offset($offset);

        $totalSelect->columns(array('count' => 'count(1)'));
        if (!empty($order)) {
            $select->order($order);
        }
        if (!empty($group)) {
            $select->group($group);
        }
        if (!empty($condition)) {
            $select->where($condition);
            $totalSelect->where($condition);
        }

        if (!empty($join)) {
            foreach ($join as $type => $info) {
                if (empty($info)) {
                    continue;
                }

                if ($type === 'LEFT_JOIN') {
                    $select->leftJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->leftJoin($info['table'], $info['on']);
                    continue;
                }

                if ($type === 'INNER_JOIN') {
                    $select->innerJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->innerJoin($info['table'], $info['on']);
                    continue;
                }

                if ($type === 'RIGHT_JOIN') {
                    $select->rightJoin($info['table'], $info['on'], $info['columns']);
                    $totalSelect->rightJoin($info['table'], $info['on']);
                    continue;
                }
            }
        }

        $rows = $this->database->select($select);
        $total = $this->database->select($totalSelect, Select::SINGLE);
        $totalCount = intval($total['count']);
        return array(
            'totalCount' => $totalCount,
            'totalPage' => ceil($totalCount / $pageSize),
            'list' => $rows
        );
    }
}
