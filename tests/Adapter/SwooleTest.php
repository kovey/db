<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-22 14:48:01
 *
 */
namespace Kovey\Db\Adapter;

use PHPUnit\Framework\TestCase;
use Kovey\Db\Exception\DbException;

class SwooleTest extends TestCase
{
    protected $swoole;

    protected function setUp() : void
    {
        $this->swoole = new Swoole(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));
        $this->swoole->connect();
        $this->swoole->exec('create table test (id int AUTO_INCREMENT, name varchar(512) NOT NULL DEFAULT \'\', PRIMARY KEY (id))');
    }

    protected function tearDown() : void
    {
        $this->swoole->exec('drop table test');
    }

    public function testConnectSuccess()
    {
        $swoole = new Swoole(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));

        $result = $swoole->connect();
        $this->assertTrue($result);
    }

    public function testConnectFailure()
    {
        $swoole = new Swoole(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF-8',
            'options' => array()
        )));

        $result = $swoole->connect();
        $this->assertFalse($result);
        $this->assertEquals('Unknown charset [UTF-8]', $swoole->getError());
    }

    public function testExecSuccess()
    {
        $this->swoole->exec('insert into test(name) values ("this is test")');
        $rows = $this->swoole->query('select * from test');
        $this->swoole->exec('update test set name = "this is update" where id = 1');
        $upRows = $this->swoole->query('select * from test');
        $this->swoole->exec('delete from test where id = 1');
        $delRows = $this->swoole->query('select * from test');
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is test')), $rows);
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is update')), $upRows);
        $this->assertEquals(array(), $delRows);
    }

    public function testQuerySuccess()
    {
        $rows = $this->swoole->query('select * from test');
        $this->assertEquals(array(), $rows);
    }

    public function testPrepareSuccess()
    {
        $sth = $this->swoole->prepare('insert into test(name) values (?)');
        $this->assertTrue($sth->execute(array('this is test')));
        $rows = $this->swoole->query('select * from test');
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is test')), $rows);
    }

    public function testTransactionSuccess()
    {
        $this->assertTrue($this->swoole->beginTransaction());
        try {
            $sth = $this->swoole->prepare('insert into test(name) values (?)');
            $this->assertTrue($sth->execute(array('this is test')));
            $sth = $this->swoole->prepare('insert into test(name) values (?)');
            $this->assertTrue($sth->execute(array('this is test1')));
            $this->assertTrue($this->swoole->commit());
        } catch (DbException $e) {
            $this->swoole->rollBack();
        }
        $rows = $this->swoole->query('select * from test');
        $this->assertEquals(array(
            array('id' => 1, 'name' => 'this is test'),
            array('id' => 2, 'name' => 'this is test1'),
        ), $rows);
    }

    public function testTransactionFailure()
    {
        $this->swoole->beginTransaction();
        try {
            $sth = $this->swoole->prepare('insert into test(name) values (?)');
            $sth->execute(array('this is test'));
            $sth = $this->swoole->prepare('insert into test1(name) values (?)');
            $sth->execute(array('this is test1'));
            $this->swoole->commit();
        } catch (DbException $e) {
            $this->assertTrue($this->swoole->rollBack());
        }
        $rows = $this->swoole->query('select * from test');
        $this->assertEquals(array(), $rows);
    }

    public function testFetch()
    {
        $this->swoole->exec('insert into test(name) values ("this is test")');
        $sth = $this->swoole->prepare('select * from test where id = ?');
        $sth->execute(array(1));
        $this->assertEquals(array('id' => 1, 'name' => 'this is test'), $this->swoole->fetch($sth));
    }

    public function testAffectedRows()
    {
        $sth = $this->swoole->prepare('insert into test(name) values (?)');
        $sth->execute(array('this is test'));
        $this->assertEquals(1, $this->swoole->affectedRows($sth));
    }

    public function testError()
    {
        try {
            $this->swoole->query('select * from test1');
        } catch (DbException $e) {
            $this->assertEquals('1146', $this->swoole->errorCode());
            $this->assertEquals("SQLSTATE[42S02] [1146] Table 'test.test1' doesn't exist", $this->swoole->errorInfo());
        }
    }
}
