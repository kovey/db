<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-28 10:53:45
 *
 */
namespace Kovey\Db;

use PHPUnit\Framework\TestCase;
use Kovey\Db\Sql\Insert;
use Kovey\Db\Sql\BatchInsert;
use Kovey\Db\Sql\Update;
use Kovey\Db\Sql\Select;
use Kovey\Db\Sql\Delete;

class MysqlTest extends TestCase
{
    protected $mysql;

    protected function setUp() : void
    {
        $this->mysql = new Mysql(array(
            'dbname' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'adapter' => Adapter::DB_ADAPTER_PDO,
            'options' => array()
        ));
        $this->mysql->connect();
        $this->mysql->exec('create table test (id int AUTO_INCREMENT, name varchar(512) NOT NULL DEFAULT \'\', PRIMARY KEY (id))');
    }

    public function testConnect()
    {
        $mysql = new Mysql(array(
            'dbname' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'adapter' => Adapter::DB_ADAPTER_PDO,
            'options' => array()
        ));
        $this->assertTrue($mysql->connect());
    }

    public function testGetError()
    {
        $mysql = new Mysql(array(
            'dbname' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF-8',
            'adapter' => Adapter::DB_ADAPTER_PDO,
            'options' => array()
        ));
        $this->assertFalse($mysql->connect());
        $this->assertEquals('2019 SQLSTATE[HY000] [2019] Unknown character set', $mysql->getError());
    }

    public function testQuery()
    {
        $rows = $this->mysql->query('select * from test');
        $this->assertEquals(array(), $rows);
    }

    public function testInsert()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->assertEquals(1, $this->mysql->insert($insert));
        $this->assertEquals(array(array(
            'id' => 1,
            'name' => 'kovey framework'
        )), $this->mysql->query('select * from test'));
    }

    public function testBatchInsert()
    {
        $batchInsert = new BatchInsert('test');
        for ($i = 0; $i < 3; $i ++) {
            $insert = new Insert('test');
            $insert->name = 'kovey framework' . $i;
            $batchInsert->add($insert);
        }

        $this->assertEquals(3, $this->mysql->batchInsert($batchInsert));
        $this->assertEquals(array(
            array(
                'id' => 1,
                'name' => 'kovey framework0'
            ),
            array(
                'id' => 2,
                'name' => 'kovey framework1'
            ),
            array(
                'id' => 3,
                'name' => 'kovey framework2'
            ),
        ), $this->mysql->query('select * from test'));
    }

    public function testUpdate()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->mysql->insert($insert);

        $update = new Update('test');
        $update->name = 'kovey framework update';
        $update->where(array('id' => 1));
        $this->assertEquals(1, $this->mysql->update($update));
        $this->assertEquals(array(array(
            'id' => 1,
            'name' => 'kovey framework update'
        )), $this->mysql->query('select * from test'));
    }

    public function testDelete()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->mysql->insert($insert);

        $delete = new Delete('test');
        $delete->where(array('id' => 1));
        $this->assertEquals(1, $this->mysql->delete($delete));
        $this->assertEquals(array(), $this->mysql->query('select * from test'));
    }

    public function testSelect()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->mysql->insert($insert);

        $select = new Select('test');
        $select->columns(array('id', 'username' => 'name'))
            ->where(array('id' => 1));
        $this->assertEquals(array('id' => 1, 'username' => 'kovey framework'), $this->mysql->select($select, $select::SINGLE));
        $this->assertEquals(array(array('id' => 1, 'username' => 'kovey framework')), $this->mysql->select($select, $select::ALL));
    }

    public function testTransaction()
    {
        $this->assertTrue($this->mysql->beginTransaction());
        try {
            $insert = new Insert('test');
            $insert->name = 'kovey framework';
            $this->mysql->insert($insert);
            $insert = new Insert('test');
            $insert->name = 'kovey framework one';
            $this->mysql->insert($insert);
            $this->assertTrue($this->mysql->commit());
        } catch (DbException $e) {
            $this->mysql->rollBack();
        }

        $this->assertEquals(array(
            array(
                'id' => 1,
                'name' => 'kovey framework'
            ),
            array(
                'id' => 2,
                'name' => 'kovey framework one'
            )
        ), $this->mysql->query('select * from test'));
    }

    public function testTransactionFun()
    {
        $this->assertTrue($this->mysql->transation(function ($db, $param) {
            $insert = new Insert('test');
            $insert->name = 'kovey framework';
            $db->insert($insert);
            $insert = new Insert('test');
            $insert->name = 'kovey framework one';
            $db->insert($insert);
        }, function ($db, $param) {
            $this->assertInstanceOf(DbInterface::class, $db);
            $this->assertInstanceOf(Mysql::class, $db);
            $this->assertEquals('transation test', $param);
        }, 'transation test'));

        $this->assertEquals(array(
            array(
                'id' => 1,
                'name' => 'kovey framework'
            ),
            array(
                'id' => 2,
                'name' => 'kovey framework one'
            )
        ), $this->mysql->query('select * from test'));
    }

    public function testFetchRow()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->mysql->insert($insert);

        $this->assertEquals(array('id' => 1, 'name' => 'kovey framework'), $this->mysql->fetchRow('test', array('id' => 1), array('id', 'name')));
    }

    public function testFetchAll()
    {
        $insert = new Insert('test');
        $insert->name = 'kovey framework';
        $this->mysql->insert($insert);

        $this->assertEquals(array(array('id' => 1, 'name' => 'kovey framework')), $this->mysql->fetchAll('test', array('id' => 1), array('id', 'name')));
    }

    public function testInTransaction()
    {
        $this->mysql->beginTransaction();
        $this->assertTrue($this->mysql->inTransaction());
        $this->mysql->rollBack();
        $this->assertFalse($this->mysql->inTransaction());
        $this->mysql->beginTransaction();
        $this->assertTrue($this->mysql->inTransaction());
        $this->mysql->commit();
        $this->assertFalse($this->mysql->inTransaction());
    }

    protected function tearDown() : void
    {
        $this->mysql->exec('drop table test');
    }
}
