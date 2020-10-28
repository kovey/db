<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-22 18:13:15
 *
 */
namespace Kovey\Db\Sharding;

require_once __DIR__ . '/Cases/Table.php';

use PHPUnit\Framework\TestCase;
use Kovey\Db\Mysql;
use Kovey\Db\Adapter;
use Kovey\Db\Model\Cases\Table;

class BaseTest extends TestCase
{
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
        $this->mysql->exec('create table test (id int AUTO_INCREMENT, number int, PRIMARY KEY (id))');
    }

    public function testInsert()
    {
        $table = new Table();
        $this->assertEquals(1, $table->insert(array(
            'number' => 1
        ), $this->mysql));

        $this->assertEquals(array('id' => 1, 'number' => 1), $table->fetchRow(array('id' => 1), array('id', 'number'), $this->mysql));
    }

    public function testUpdate()
    {
        $table = new Table();
        $table->insert(array(
            'number' => 1
        ), $this->mysql);

        $this->assertEquals(1, $table->update(array(
            'number' => 3
        ), array('id' => 1), $this->mysql));

        $this->assertEquals(array('id' => 1, 'number' => 3), $table->fetchRow(array('id' => 1), array('id', 'number'), $this->mysql));
    }

    public function testDelete()
    {
        $table = new Table();
        $this->assertEquals(1, $table->insert(array(
            'number' => 1
        ), $this->mysql));

        $this->assertEquals(array('id' => 1, 'number' => 1), $table->fetchRow(array('id' => 1), array('id', 'number'), $this->mysql));

        $this->assertEquals(1, $table->delete(array('id' => 1), $this->mysql));
        $this->assertFalse($table->fetchRow(array('id' => 1), array('id', 'number'), $this->mysql));
    }

    public function testFetchRow()
    {
        $table = new Table();
        $table->insert(array(
            'number' => 1
        ), $this->mysql);

        $this->assertEquals(array('id' => 1, 'number' => 1), $table->fetchRow(array('id' => 1), array('id', 'number'), $this->mysql));
    }

    public function testFetchAll()
    {
        $table = new Table();
        $table->insert(array(
            'number' => 1
        ), $this->mysql);
        $table->insert(array(
            'number' => 2
        ), $this->mysql);

        $this->assertEquals(array(
            array('id' => 1, 'number' => 1),
            array('id' => 2, 'number' => 2),
        ), $table->fetchAll(array(), array('id', 'number'), $this->mysql));
    }

    public function testBatchInsert()
    {
        $table = new Table();
        $this->assertEquals(2, $table->batchInsert(array(
            array(
                'number' => 1
            ),
            array(
                'number' => 2
            )
        ), $this->mysql));
        $this->assertEquals(array(
            array('id' => 1, 'number' => 1),
            array('id' => 2, 'number' => 2),
        ), $table->fetchAll(array(), array('id', 'number'), $this->mysql));
    }

    protected function tearDown() : void
    {
        $this->mysql->exec('drop table test');
    }
}
