<?php
/**
 *
 * @description 更新语句实现
 *
 * @package     Db\Sql
 *
 * @time        Tue Sep 24 09:04:53 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sql;

use Kovey\Db\SqlInterface;

class Update implements SqlInterface
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
    const SQL_FORMAT = 'UPDATE %s SET %s';

    /**
     * @description 条件
     *
     * @var Where
     */
    private Where $where;

    /**
     * @description 原始数据
     *
     * @var Array
     */
    private Array $orignalData = array();

    /**
     * @description 自增数据
     *
     * @var Array
     */
    private Array $addData = array();

    /**
     * @description 自减数据
     *
     * @var Array
     */
    private Array $subData = array();

    /**
     * @description 直更数据
     *
     * @var Array
     */
    private Array $equalData = array();

    /**
     * @description 是否解析标志
     *
     * @var bool
     */
    private bool $isParsed = false;

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
     * @description 设置直更值
     *
     * @param string $name
     *
     * @param string $val
     *
     * @return null
     */
    public function __set(string $name, $val) : void
    {
        $this->orignalData[$name] = $val;
        $this->equalData[$name] = $val;
    }

    /**
     * @description 解析数据
     *
     * @return Update
     */
    protected function parseData() : Update
    {
        if ($this->isParsed) {
            return $this;
        }

        $this->isParsed = true;
        foreach ($this->equalData as $name => $val) {
            $this->fields[] = $this->format($name) . '=?';
            $this->data[] = $val;
        }

        foreach ($this->addData as $name => $val) {
            $this->fields[] = $this->format($name) . '= ' . $this->format($name) . ' + ?';
            $this->data[] = $val;
        }

        foreach ($this->subData as $name => $val) {
            $this->fields[] = $this->format($name) . '= ' . $this->format($name) . ' - ?';
            $this->data[] = $val;
        }

        return $this;
    }

    /**
     * @description 获取字段值
     *
     * @param string $name
     *
     * @return string
     */
    public function __get(string $name)
    {
        return $this->orignalData[$name] ?? '';
    }

    /**
     * @description 自增字段
     *
     * @param string $name
     *
     * @param int $val
     *
     * @return Update
     */
    public function addSelf(string $name, int $val) : Update
    {
        $this->orignalData[$name] = $val;
        $this->addData[$name] = $val;
        return $this;
    }

    /**
     * @description 自减字段
     *
     * @param string $name
     *
     * @param int $val
     *
     * @return Update
     */
    public function subSelf(string $name, int $val)
    {
        $this->orignalData[$name] = $val;
        $this->subData[$name] = $val;
        return $this;
    }

    /**
     * @description 条件
     *
     * @param Array | Where $condition
     *
     * @return Update
     */
    public function where(Array | Where $condition) : Update
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
     * @return string | bool
     */
    public function getPrepareSql() : ? string
    {
        $this->parseData();

        if (count($this->fields) < 1 || count($this->data) < 1) {
            return null;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields)); 
        $whereSql = $this->where->getPrepareWhereSql();
        if (!empty($whereSql)) {
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
        $tmp = $this->data;
        foreach ($this->where->getBindData() as $val) {
            $tmp[] = $val;
        }

        return $tmp;
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
