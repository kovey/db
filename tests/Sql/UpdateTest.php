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

class UpdateTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $update = new Update('test');
        $update->name = 'update';
        $update->age = 20;
        $this->assertEquals('update', $update->name);
        $this->assertEquals(20, $update->age);
        $update->name = 'update1';
        $this->assertEquals('update1', $update->name);
        $update->where(array('id' => 1));

        $this->assertEquals('UPDATE `test` SET `name`=?,`age`=? WHERE (`id` = ?)', $update->getPrepareSql());
        $this->assertEquals(array('update1', 20, 1), $update->getBindData());
        $this->assertEquals("UPDATE `test` SET `name`='update1',`age`='20' WHERE (`id` = '1')", $update->toString());
    }
}
