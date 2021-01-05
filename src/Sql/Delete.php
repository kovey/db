<?php
/**
 *
 * @description 删除
 *
 * @package     Db\Sql
 *
 * @time        2020-03-07 12:02:48
 *
 * @author      kovey
 */
namespace Kovey\Db\Sql;

use Kovey\Db\SqlInterface;

class Delete implements SqlInterface
{
    /**
     * @description 表名
     *
     * @var string
     */
    private string $table;

    /**
     * @description 更新的字段
     *
     * @var Array
     */
    private Array $fields = array();

    /**
     * @description 字段的值
     *
     * @var Array
     */
    private Array $data = array();

    /**
     * @description 更新格式
     *
     * @var string
     */
    const SQL_FORMAT = 'DELETE FROM %s';

    /**
     * @description 条件
     *
     * @var Where
     */
    private Where $where;

    /**
     * @description 构造
     *
     * @var string $table
     */
    public function __construct(string $table)
    {
        $this->where = new Where();
        $info = explode('.', $table);
        array_walk($info, function (&$row) {
            $row = $this->format($row);
        });

        $this->table = implode('.', $info);
    }

    /**
     * @description 格式化字段
     *
     * @param string $name
     *
     * @return string
     */
    private function format(string $name) : string
    {
        return sprintf('`%s`', $name);
    }

    /**
     * @description 条件
     *
     * @param Array $condition
     *
     * @return Update
     */
    public function where(Array | Where $condition) : Delete
    {
        if ($condition instanceof Where) {
            $this->where = $condition;
            return $this;
        }

        foreach ($condition as $key => $val) {
            if (is_numeric($key)) {
                $this->where->statement($val);
                continue;
            }

            if (is_array($val)) {
                $this->where->in($key, $val);
                continue;
            }

            $this->where->eq($key, $val);
        }

        return $this;
    }

    /**
     * @description 准备SQL语句
     *
     * @return string | null
     */
    public function getPrepareSql() : ? string
    {
        $sql = sprintf(self::SQL_FORMAT, $this->table); 
        $whereSql = $this->where->getPrepareWhereSql();
        if ($whereSql !== false) {
            $sql .= $whereSql; 
        }

        return $sql;
    }

    /**
     * @description 获取绑定数据
     *
     * @return Array
     */
    public function getBindData() : Array
    {
        return $this->where->getBindData();
    }

    /**
     * @description 格式化SQL
     *
     * @return string
     */
    public function toString() : string
    {
        $sql = $this->getPrepareSql();
        $data = $this->getBindData();
        if (count($data) < 1) {
            return $sql;
        }

        foreach ($data as $needle) {
            $sql = substr_replace($sql, '\'' . $needle . '\'', strpos($sql, '?'), 1);
        }

        return $sql;
    }

    public function __toString() : string
    {
        return $this->toString();
    }
}
