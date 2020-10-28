<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-28 11:44:06
 *
 */
namespace Kovey\Db\Sql;

use PHPUnit\Framework\TestCase;

class WhereTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $where = new Where();
        $where->name = 'name';
        $where->gt('id', 1);
        $where->neq('name', 'test');
        $where->ge('age', 18);
        $where->lt('id', 10);
        $where->le('age', 30);
        $where->eq('case', 11);
        $where->in('nickname', array('kovey', 'framework'));
        $where->nin('account', array('map', 'array'));
        $where->like('signature', '%aa%');
        $where->between('height', 160, 180);
        $where->statement('abc = 123');

        $this->assertEquals(' WHERE (`name` = ? AND `id` > ? AND `name` <> ? AND `age` >= ? AND `id` < ? AND `age` <= ? AND `case` = ? AND `nickname` IN (?,?) AND `account` NOT IN (?,?) AND `signature` LIKE ? AND `height` BETWEEN ? AND ? AND abc = 123)', $where->getPrepareWhereSql());
        $this->assertEquals(' WHERE (`name` = ? OR `id` > ? OR `name` <> ? OR `age` >= ? OR `id` < ? OR `age` <= ? OR `case` = ? OR `nickname` IN (?,?) OR `account` NOT IN (?,?) OR `signature` LIKE ? OR `height` BETWEEN ? AND ? OR abc = 123)', $where->getPrepareOrWhereSql());
        $this->assertEquals(array('name', 1, 'test', 18, 10, 30, 11, 'kovey', 'framework', 'map', 'array', '%aa%', 160, 180), $where->getBindData());
    }

    public function testToString()
    {
        $where = new Where();
        $where->name = 'name';
        $where->id = 1;

        $this->assertEquals(" WHERE (`name` = 'name' AND `id` = '1')", $where->toString());
    }
}
