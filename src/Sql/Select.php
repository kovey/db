<?php
/**
 *
 * @description 查询语句实现
 *
 * @package     Db\Sql
 *
 * @time        Tue Sep 24 09:04:25 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sql;

use Kovey\Db\SqlInterface;

class Select implements SqlInterface
{
    /**
     * @description 单条查询
     *
     * @var int
     */
    const SINGLE = 1;

    /**
     * @description 查询全部
     *
     * @var int
     */
    const ALL = 2;

    /**
     * @description 表名
     *
     * @var string
     */
    private string $table;

    /**
     * @description 查询字段
     *
     * @var Array
     */
    private Array $fields = array();

    /**
     * @description SQL格式
     *
     * @var string
     */
    const SQL_FORMAT = 'SELECT %s FROM %s';

    /**
     * @description 内联语法
     *
     * @var string
     */
    const INNER_JOIN_FORMAT = ' INNER JOIN %s AS %s ON %s ';

    /**
     * @description 左联语法
     *
     * @var string
     */
    const LEFT_JOIN_FORMAT = ' LEFT JOIN %s AS %s ON %s ';

    /**
     * @description 右联语法
     *
     * @var string
     */
    const RIGHT_JOIN_FORMAT = ' RIGHT JOIN %s AS %s ON %s ';

    /**
     * @description 字段格式化语法
     *
     * @var string
     */
    const FIELD_FORMAT = '%s.%s as %s';

    /**
     * @description 字段常规模式语法
     *
     * @var string
     */
    const FIELD_NORMAL_FORMAT = '%s as %s';

    /**
     * @description wher条件
     *
     * @var Where
     */
    private Where $where;

    /**
     * @description OR Where条件
     *
     * @var Where
     */
    private Where $orWhere;

    /**
     * @description join数据
     *
     * @var Array
     */
    private Array $joins = array();

    /**
     * @description limit
     *
     * @var int
     */
    private int $limit = 0;

    /**
     * @description offset
     *
     * @var int
     */
    private int $offset = 0;

    /**
     * @description order
     *
     * @var string
     */
    private string $order;

    /**
     * @description group
     *
     * @var string
     */
    private string $group;

    /**
     * @description HAVING
     *
     * @var string
     */
    private Having $having;

    /**
     * @description 表别名
     *
     * @var string
     */
    private string | bool $tableAs;

    /**
     * @description 构造函数
     *
     * @param string $table
     *
     * @param string | bool $as
     */
    public function __construct(string $table, string | bool $as = false)
    {
        if (empty($as)) {
            $this->tableAs = false;
        } else {
            $this->tableAs = $as;
        }
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
        $info = explode('.', $name);
        $len = count($info);

        if ($len > 1) {
            $info[$len - 1] = sprintf('`%s`', $info[$len - 1]);
            return implode('.', $info);
        }

        $info = explode(' ', $name);
        $len = count($info);

        if ($len > 1) {
            $info[$len - 1] = sprintf('`%s`', $info[$len - 1]);
            return implode(' ', $info);
        }

        return sprintf('`%s`', $name);
    }

    /**
     * @description 查询列
     *
     * @param Array $columns
     *
     * @param string | bool $tableName
     *
     * @return Select
     */
    public function columns(Array $columns, string | bool $tableName = false) : Select
    {
        $finalTable = $this->table;
        if ($tableName === false || !is_string($tableName)) {
            if ($this->tableAs !== false && is_string($this->tableAs)) {
                $finalTable = $this->tableAs;
            }
        } else {
            $finalTable = $tableName;
        }

        foreach ($columns as $key => $val) {
            if (is_numeric($key)) {
                $key = $val;
            }

            if (preg_match('/\(/', $val)) {
                $info = explode('(', $val);
                if (strtoupper(trim($info[0])) == 'COUNT') {
                    $this->fields[] = sprintf(self::FIELD_NORMAL_FORMAT, $val, $key);
                    continue;
                }

                $val = str_replace(array('(', ')'), array('(' . $finalTable . '.`', '`)'), $val);
                $this->fields[] = sprintf(self::FIELD_NORMAL_FORMAT, $val, $key);
                continue;
            }

            $this->fields[] = sprintf(self::FIELD_FORMAT, $finalTable, $this->format($val), $key);
        }

        return $this;
    }

    /**
     * @description 关联
     *
     * @param Array $tableInfo
     *
     * @param string $on
     *
     * @param Array $fields
     *
     * @param int $type
     *
     * @return Select
     */
    private function join(Array $tableInfo, string $on, Array $fileds, string $type) : Select
    {
        $on = $this->formatOn($on);

        $as = '';
        $table = '';
        foreach ($tableInfo as $key => $val) {
            $table = $this->format($val);
            if (is_numeric($key)) {
                $as = $val;
            } else {
                $as = $key;
            }
            break;
        }

        $this->columns($fileds, $as);

        $this->joins[] = sprintf($type, $table, $as, $on);

        return $this;
    }

    /**
     * @description 内联
     *
     * @param Array $tableInfo
     *
     * @param string $on
     *
     * @param Array $fileds
     *
     * @return Select
     */
    public function innerJoin(Array $tableInfo, string $on, Array $fileds = array()) : Select
    {
        return $this->join($tableInfo, $on, $fileds, self::INNER_JOIN_FORMAT);
    }

    /**
     * @description 左联
     *
     * @param Array $tableInfo
     *
     * @param string $on
     *
     * @param Array $fileds
     *
     * @return Select
     */
    public function leftJoin(Array $tableInfo, string $on, Array $fileds = array()) : Select
    {
        return $this->join($tableInfo, $on, $fileds, self::LEFT_JOIN_FORMAT);
    }

    /**
     * @description 右联
     *
     * @param Array $tableInfo
     *
     * @param string $on
     *
     * @param Array $fileds
     *
     * @return Select
     */
    public function rightJoin(Array $tableInfo, string $on, Array $fileds = array()) : Select
    {
        return $this->join($tableInfo, $on, $fileds, self::RIGHT_JOIN_FORMAT);
    }

    /**
     * @description 查询条件
     *
     * @param Where | Array $where
     *
     * @return Select
     */
    public function where(Where | Array $where) : Select
    {
        if ($where instanceof Where) {
            $this->where = $where;
        } else if (is_array($where)) {
            $this->where = new Where();
            foreach ($where as $key => $val) {
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
        } else {
            $this->where = new Where();
            $this->where->statement($where);
        }

        return $this;
    }

    /**
     * @description 或条件
     *
     * @param Where
     *
     * @return Select
     */
    public function orWhere(Where $where) : Select
    {
        $this->orWhere = $where;
        return $this;
    }

    /**
     * @description Having过滤条件
     *
     * @param Having | Array | string $having
     *
     * @return Select
     */
    public function having(Having | Array | string $having) : Select
    {
        if ($having instanceof Having) {
            $this->having = $having;
        } else if (is_array($having)) {
            $this->having = new Having();
            foreach ($having as $key => $val) {
                if (is_numeric($key)) {
                    $this->having->statement($val);
                    continue;
                }

                if (is_array($val)) {
                    $this->having->in($key, $val);
                    continue;
                }

                $this->having->eq($key, $val);
            }
        } else {
            $this->having = new Having();
            $this->having->statement($having);
        }

        return $this;
    }

    /**
     * @description 处理表的别名
     *
     * @return string
     */
    private function processAsTable() : string
    {
        if ($this->tableAs === false) {
            return $this->table;
        }

        if (!is_string($this->tableAs)) {
            return $this->table;
        }

        return sprintf('%s AS %s', $this->table, $this->tableAs);
    }

    /**
     * @description 准备语句
     *
     * @return string
     */
    public function getPrepareSql() : ? string
    {
        $finalTable = $this->processAsTable();

        $sql = '';
        if (count($this->fields) < 1) {
            $sql = sprintf(self::SQL_FORMAT, '*', $finalTable); 
        } else {
            $sql = sprintf(self::SQL_FORMAT, implode(',', $this->fields), $finalTable);
        }

        if (count($this->joins) > 0) {
            $sql .= implode('', $this->joins);
        }

        $whereSql = $this->getPrepareWhere();
        if (!empty($whereSql)) {
            $sql .= $whereSql;
        }

        if (!empty($this->group)) {
            $sql .= $this->group;
        }

        if (!empty($this->having)) {
            $hsql = $this->having->getPrepareHavingSql();
            if (!empty($hsql)) {
                $sql .= $hsql;
            }
        }

        if (!empty($this->order)) {
            $sql .= $this->order;
        }

        $limit = $this->getLimit();
        if (!empty($limit)) {
            $sql .= $limit;
        }

        return $sql;
    }

    /**
     * @description get limit
     *
     * @return string
     */
    private function getLimit() : ?string
    {
        if ($this->limit < 1) {
            return null;
        }

        return sprintf(' LIMIT %s,%s', $this->offset, $this->limit);
    }

    /**
     * @description 准备查询条件
     *
     * @return string
     */
    private function getPrepareWhere() : ? string
    {
        $sql = null;
        if (!empty($this->where)) {
            $sql = $this->where->getPrepareWhereSql();
        }

        if (!empty($this->orWhere)) {
            if (empty($sql)) {
                $sql = $this->orWhere->getPrepareOrWhereSql();
            } else {
                $sql .= str_replace('WHERE', 'OR', $this->orWhere->getPrepareOrWhereSql());
            }
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
        $tmp = array();
        if (!empty($this->where)) {
            $tmp = array_merge($tmp, $this->where->getBindData()); 
        }

        if (!empty($this->orWhere)) {
            $tmp = array_merge($tmp, $this->orWhere->getBindData()); 
        }

        if (!empty($this->having)) {
            $tmp = array_merge($tmp, $this->having->getBindData()); 
        }

        return $tmp;
    }

    /**
     * @description 条数限制
     *
     * @param int $size
     *
     * @return Select
     */
    public function limit(int $size) : Select
    {
        if ($size < 1) {
            return $this;
        }

        $this->limit = $size;
        return $this;
    }

    /**
     * @description offset
     *
     * @param int $offset
     *
     * @return Select
     */
    public function offset(int $offset) : Select
    {
        if ($offset < 0) {
            return $this;
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * @description 排序
     *
     * @param string $order
     *
     * @return Select
     */
    public function order(string $order) : Select
    {
        if (!is_array($order)) {
            $order = array($order);
        }

        array_walk($order, function (&$row) {
            $tmp = explode(' ', trim($row));
            $tmp[0] = $this->format($tmp[0]);
            $row = implode(' ', $tmp);
        });

        $this->order = sprintf(' ORDER BY %s', implode(',', $order));
        return $this;
    }

    /**
     * @description 分组
     *
     * @param string | Array $group
     *
     * @return Select
     */
    public function group(string | Array $group) : Select
    {
        if (!is_array($group)) {
            $group = array($group);
        }

        array_walk($group, function (&$row) {
            $row = $this->format($row);
        });

        $this->group = sprintf(' GROUP BY %s', implode(',', $group));
        return $this;
    }

    /**
     * @description 格式化ON条件
     *
     * @param string $on
     *
     * @return string
     */
    private function formatOn(string $on) : string
    {
        $info = explode(' ', $on);
        array_walk($info, function (&$row) {
            if (empty(trim($row))) {
                return;
            }

            if (in_array(strtoupper(trim($row)), array('AND', 'OR'))) {
                return;
            }

            $tmp = explode('=', $row);
            if (count($tmp) != 2) {
                return;
            }

            array_walk($tmp, function (&$r) {
                $tt = explode('.', trim($r));
                $len = count($tt);
                if ($len < 2) {
                    return;
                }

                $tt[$len - 1] = sprintf('`%s`', $tt[$len - 1]);

                $r = implode('.', $tt);
            });

            $row = implode('=', $tmp);
        });

        return implode(' ', $info);
    }

    /**
     * @description 格式化SQL语句
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
