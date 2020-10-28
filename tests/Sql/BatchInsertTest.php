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

class BatchInsertTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $batchInsert = new BatchInsert('test');
        for ($i = 0; $i < 10; $i ++) {
            $insert = new Insert('test');
            $insert->name = 'name' . $i;
            $insert->password = 'password' . $i;
            $batchInsert->add($insert);
        }

        $this->assertEquals('INSERT INTO `test` (`name`,`password`) VALUES (?,?),(?,?),(?,?),(?,?),(?,?),(?,?),(?,?),(?,?),(?,?),(?,?)', $batchInsert->getPrepareSql());
        $this->assertEquals(array('name0', 'password0', 'name1', 'password1', 'name2', 'password2', 'name3', 'password3', 'name4', 'password4', 'name5', 'password5', 'name6', 'password6', 'name7', 'password7', 'name8', 'password8', 'name9', 'password9'), $batchInsert->getBindData());
    }

    public function testToString()
    {
        $batchInsert = new BatchInsert('test');
        for ($i = 0; $i < 10; $i ++) {
            $insert = new Insert('test');
            $insert->name = 'name' . $i;
            $insert->password = 'password' . $i;
            $batchInsert->add($insert);
        }

        $this->assertEquals("INSERT INTO `test` (`name`,`password`) VALUES ('name0','password0'),('name1','password1'),('name2','password2'),('name3','password3'),('name4','password4'),('name5','password5'),('name6','password6'),('name7','password7'),('name8','password8'),('name9','password9')", $batchInsert->toString());
    }
}
