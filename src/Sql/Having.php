<?php
/**
 * @description Having条件
 *
 * @package Db\Sql
 *
 * @author kovey
 *
 * @time 2020-03-23 22:49:07
 *
 */
namespace Kovey\Db\Sql;

class Having
{
    /**
     * @description having语法格式
     *
     * @var string
     */
    const SQL_FORMAT = ' HAVING (%s)';

    /**
     * @description 条件数据
     *
     * @var Array
     */
    private Array $data;

    /**
     * @description 条件字段
     *
     * @var Array
     */
    private Array $fields;

    /**
     * @description 构造函数
     */
    public function __construct()
    {
        $this->data = array();
        $this->fields = array();
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
        $info = explode('.', $name);
        $len = count($info);

        if ($len > 1) {
            $info[$len - 1] = sprintf('`%s`', $info[$len - 1]);
            return implode('.', $info);
        }

        return sprintf('`%s`', $name);
    }

    /**
     * @description 设置条件值
     *
     * @param string $name
     *
     * @param string | int $val
     *
     * @return void
     */
    public function __set(string $name, string | int $val) : void
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '=?';
    }

    /**
     * @description 大于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return Having
     */
    public function gt(string $name, int | string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>?';
        return $this;
    }

    /**
     * @description 不等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return Having
     */
    public function neq(string $name, int | string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<>?';
        return $this;
    }

    /**
     * @description 大于等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return Having
     */
    public function ge(string $name, int | string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '>=?';
        return $this;
    }

    /**
     * @description 小于
     *
     * @param string $name
     *
     * @param int | string  $val
     *
     * @return Having
     */
    public function lt(string $name, int | string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<?';
        return $this;
    }

    /**
     * @description 小于等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return Having
     */
    public function le(string $name, int | string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . '<=?';
        return $this;
    }

    /**
     * @description 等于
     *
     * @param string $name
     *
     * @param int | string  $val
     *
     * @return Having
     */
    public function eq(string $name, int | string $val) : Having
    {
        $this->__set($name, $val);
        return $this;
    }

    /**
     * @description IN
     *
     * @param string $name
     *
     * @param Array $val
     *
     * @return Having
     */
    public function in(string $name, Array $val) : Having
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' IN(' . implode(',', $inVals). ')';
        return $this;
    }

    /**
     * @description NOT IN
     *
     * @param string $name
     *
     * @param Array $val
     *
     * @return Having
     */
    public function nin(string $name, Array $val) : Having
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' NOT IN(' . implode(',', $inVals) . ')';
        return $this;
    }

    /**
     * @description LIKE
     *
     * @param string $name
     *
     * @param string $val
     *
     * @return Having
     */
    public function like(string $name, string $val) : Having
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' LIKE ?';
        return $this;
    }

    /**
     * @description BETWEEN
     *
     * @param string $name
     *
     * @param int | string $start
     *
     * @param int | string  $end
     *
     * @return Having
     */
    public function between(string $name, int | string $start, int | string $end) : Having
    {
        $this->data[] = $start;
        $this->data[] = $end;
        $this->fields[] = $this->format($name) . ' BETWEEN ? AND ?';
        return $this;
    }

    /**
     * @description 语句
     *
     * @param string $statement
     *
     * @return Having
     */
    public function statement(string $statement) : Having
    {
        $this->fields[] = $statement;
        return $this;
    }

    /**
     * @description 准备SQL
     *
     * @return string
     */
    public function getPrepareHavingSql() : ? string
    {
        if (count($this->fields) < 1) {
            return null;
        }

        return sprintf(self::SQL_FORMAT, implode(' AND ', $this->fields));
    }

    /**
     * @description 获取绑定数据
     *
     * @return Array
     */
    public function getBindData() : Array
    {
        return $this->data;
    }

    /**
     * @description 格式化SQL语句
     *
     * @return string
     */
    public function toString() :string
    {
        $sql = $this->getPrepareHavingSql();
        if (count($this->data) < 1) {
            return empty($sql) ? '' : $sql;
        }

        foreach ($this->data as $needle) {
            $sql = substr_replace($sql, '\'' . $needle . '\'', strpos($sql, '?'), 1);
        }
        return $sql;
    }

    public function __toString() :string
    {
        return $this->toString();
    }
}
