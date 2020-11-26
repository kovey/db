<?php
/**
 *
 * @description where语句实现
 *
 * @package     Db\Sql
 *
 * @time        Tue Sep 24 09:05:28 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sql;

class Where
{
    /**
     * @description where语法格式
     *
     * @var string
     */
    const SQL_FORMAT = ' WHERE (%s)';

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
     * @param mixed $val
     *
     * @return null
     */
    public function __set(string $name, $val) : void
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' = ?';
    }

    /**
     * @description 大于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return null
     */
    public function gt(string $name, int | string $val) : Where
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' > ?';
        return $this;
    }

    /**
     * @description 不等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return null
     */
    public function neq(string $name, int | string $val) : Where
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' <> ?';
        return $this;
    }

    /**
     * @description 大于等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return null
     */
    public function ge(string $name, int | string $val) : Where
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' >= ?';
        return $this;
    }

    /**
     * @description 小于
     *
     * @param string $name
     *
     * @param string | int $val
     *
     * @return null
     */
    public function lt(string $name, int | string $val) : Where
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' < ?';
        return $this;
    }

    /**
     * @description 小于等于
     *
     * @param string $name
     *
     * @param int | string $val
     *
     * @return null
     */
    public function le(string $name, int | string $val) : Where
    {
        $this->data[] = $val;
        $this->fields[] = $this->format($name) . ' <= ?';
        return $this;
    }

    /**
     * @description 等于
     *
     * @param string $name
     *
     * @param string | int $val
     *
     * @return null
     */
    public function eq(string $name, string | int $val) : Where
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
     * @return null
     */
    public function in(string $name, Array $val) : Where
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' IN (' . implode(',', $inVals). ')';
        return $this;
    }

    /**
     * @description NOT IN
     *
     * @param string $name
     *
     * @param Array $val
     *
     * @return null
     */
    public function nin(string $name, Array $val) : Where
    {
        $inVals = array();
        foreach ($val as $v) {
            $this->data[] = $v;
            $inVals[] = '?';
        }

        $this->fields[] = $this->format($name) . ' NOT IN (' . implode(',', $inVals) . ')';
        return $this;
    }

    /**
     * @description LIKE
     *
     * @param string $name
     *
     * @param string $val
     *
     * @return null
     */
    public function like(string $name, string $val) : Where
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
     * @param int | string $end
     *
     * @return Where
     */
    public function between(string $name, int | string $start, int | string $end) : Where
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
     * @return null
     */
    public function statement(string $statement) : Where
    {
        $this->fields[] = $statement;
        return $this;
    }

    /**
     * @description 准备SQL
     *
     * @return string
     */
    public function getPrepareWhereSql() : ? string
    {
        if (count($this->fields) < 1) {
            return null;
        }

        return sprintf(self::SQL_FORMAT, implode(' AND ', $this->fields));
    }

    /**
     * @description 准备OR WHERE
     *
     * @return string
     */
    public function getPrepareOrWhereSql() : ? string
    {
        if (count($this->fields) < 1) {
            return null;
        }

        return sprintf(self::SQL_FORMAT, implode(' OR ', $this->fields));
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
    public function toString() : string
    {
        $sql = $this->getPrepareWhereSql();
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
