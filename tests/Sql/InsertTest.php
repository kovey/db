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

class InsertTest extends TestCase
{
    public function testSetGet()
    {
        $insert = new Insert('test');
        $insert->name = 'name';
        $insert->password = 'password';

        $this->assertEquals('name', $insert->name);
        $this->assertEquals('password', $insert->password);
    }

    public function testGetPrepareSqlAndBindData()
    {
        $insert = new Insert('test');
        $insert->name = 'name';
        $insert->password = 'password';

        $this->assertEquals('INSERT INTO `test` (`name`,`password`) VALUES (?,?)', $insert->getPrepareSql());
        $this->assertEquals(array('name', 'password'), $insert->getBindData());
    }

    public function testToString()
    {
        $insert = new Insert('test');
        $insert->name = 'name';
        $insert->password = 'password';

        $this->assertEquals("INSERT INTO `test` (`name`,`password`) VALUES ('name','password')", $insert->toString());
    }
}
