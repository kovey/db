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

class HavingTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $having = new Having();
        $having->name = 'name';
        $having->gt('id', 1);
        $having->neq('name', 'test');
        $having->ge('age', 18);
        $having->lt('id', 10);
        $having->le('age', 30);
        $having->eq('case', 11);
        $having->in('nickname', array('kovey', 'framework'));
        $having->nin('account', array('map', 'array'));
        $having->like('signature', '%aa%');
        $having->between('height', 160, 180);
        $having->statement('abc = 123');

        $this->assertEquals(' HAVING (`name`=? AND `id`>? AND `name`<>? AND `age`>=? AND `id`<? AND `age`<=? AND `case`=? AND `nickname` IN(?,?) AND `account` NOT IN(?,?) AND `signature` LIKE ? AND `height` BETWEEN ? AND ? AND abc = 123)', $having->getPrepareHavingSql());
        $this->assertEquals(array('name', 1, 'test', 18, 10, 30, 11, 'kovey', 'framework', 'map', 'array', '%aa%', 160, 180), $having->getBindData());
    }

    public function testToString()
    {
        $having = new Having();
        $having->name = 'name';
        $having->id = 1;

        $this->assertEquals(" HAVING (`name`='name' AND `id`='1')", $having->toString());
    }
}
