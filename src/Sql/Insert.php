<?php
/**
 *
 * @description 插入语句实现
 *
 * @package     Db\Sql
 *
 * @time        Tue Sep 24 09:03:58 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sql;

use Kovey\Db\SqlInterface;

class Insert implements SqlInterface
{
    /**
     * @description 表名
     *
     * @var string
     */
    private string $table;

    /**
     * @description 插入的字段
     *
     * @var Array
     */
    private Array $fields = array();

    /**
     * @description 占位符
     *
     * @var Array
     */
    private Array $values = array();

    /**
     * @description 插入的值
     *
     * @var Array
     */
    private Array $data = array();

    /**
     * @description SQL语法格式
     *
     * @var string
     */
    const SQL_FORMAT = 'INSERT INTO %s (%s) VALUES (%s)';

    /**
     * @description 原始数据
     *
     * @var Array
     */
    private Array $orignalData = array();

    /**
     * @description 是否解析过的标志,防止多次解析，导致sql语法出错
     *
     * @var bool
     */
    private bool $isParsed = false;

    /**
     * @description 构造函数
     *
     * @param string $table
     */
    public function __construct(string $table)
    {
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
     * @description 设置字段值
     *
     * @param string $name
     *
     * @param mixed $val
     *
     * @return null
     */
    public function __set(string $name, $val)
    {
        $this->orignalData[$name] = $val;
    }

    /**
     * @description 获取字段的值
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->orignalData[$name] ?? '';
    }

    /**
     * @description 解析占位符和值
     *
     * @return null
     */
    public function parseData() : void
    {
        if ($this->isParsed) {
            return;
        }

        $this->isParsed = true;
        foreach ($this->orignalData as $name => $val) {
            $this->fields[] = $this->format($name);
            $this->data[] = $val;
            $this->values[] = '?';
        }
    }

    /**
     * @description 获取字段
     *
     * @return Array
     */
    public function getFields() : Array
    {
        return $this->fields;
    }

    /**
     * @description 获取占位符
     *
     * @return Array
     */
    public function getValues() : Array
    {
        return $this->values;
    }

    /**
     * @description 准备SQL
     *
     * @return string | null
     */
    public function getPrepareSql() : ? string
    {
        $this->parseData();

        if (count($this->fields) < 1 || count($this->data) < 1) {
            return null;
        }

        $sql = sprintf(self::SQL_FORMAT, $this->table, implode(',', $this->fields), implode(',', $this->values)); 

        return $sql;
    }

    /**
     * @description 获取绑定的值
     *
     * @return Array
     */
    public function getBindData() : Array
    {
        return $this->data;
    }

    /**
     * @description 格式化SQL
     *
     * @return string
     */
    public function toString() : string
    {
        $sql = $this->getPrepareSql();
        if (count($this->data) < 1) {
            return $sql;
        }

        foreach ($this->data as $needle) {
            $sql = substr_replace($sql, '\'' . $needle . '\'', strpos($sql, '?'), 1);
        }

        return $sql;
    }

    public function __toString() : string
    {
        return $this->toString();
    }
}
