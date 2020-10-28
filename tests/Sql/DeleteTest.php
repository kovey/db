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

class DeleteTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $delete = new Delete('test');
        $delete->where(array('id' => 1));

        $this->assertEquals('DELETE FROM `test` WHERE (`id` = ?)', $delete->getPrepareSql());
        $this->assertEquals(array(1), $delete->getBindData());
    }

    public function testToString()
    {
        $delete = new Delete('test');
        $delete->where(array('id' => 1));

        $this->assertEquals("DELETE FROM `test` WHERE (`id` = '1')", $delete->toString());
    }
}
