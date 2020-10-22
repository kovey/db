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

class PdoTest extends TestCase
{
    protected $pdo;

    protected function setUp() : void
    {
        $this->pdo = new Pdo(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));
        $this->pdo->connect();
        $this->pdo->exec('create table test (id int AUTO_INCREMENT, name varchar(512) NOT NULL DEFAULT \'\', PRIMARY KEY (id))');
    }

    protected function tearDown() : void
    {
        $this->pdo->exec('drop table test');
    }

    public function testConnectSuccess()
    {
        $pdo = new Pdo(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));

        $result = $pdo->connect();
        $this->assertTrue($result);
    }

    public function testConnectFailure()
    {
        $pdo = new Pdo(new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF-8',
            'options' => array()
        )));

        $result = $pdo->connect();
        $this->assertFalse($result);
        $this->assertEquals('SQLSTATE[HY000] [2019] Unknown character set', $pdo->getError());
    }

    public function testExecSuccess()
    {
        $this->pdo->exec('insert into test(name) values ("this is test")');
        $rows = $this->pdo->query('select * from test');
        $this->pdo->exec('update test set name = "this is update" where id = 1');
        $upRows = $this->pdo->query('select * from test');
        $this->pdo->exec('delete from test where id = 1');
        $delRows = $this->pdo->query('select * from test');
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is test')), $rows);
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is update')), $upRows);
        $this->assertEquals(array(), $delRows);
    }

    public function testQuerySuccess()
    {
        $rows = $this->pdo->query('select * from test');
        $this->assertEquals(array(), $rows);
    }

    public function testPrepareSuccess()
    {
        $sth = $this->pdo->prepare('insert into test(name) values (?)');
        $this->assertTrue($sth->execute(array('this is test')));
        $rows = $this->pdo->query('select * from test');
        $this->assertEquals(array(array('id' => 1, 'name' => 'this is test')), $rows);
    }

    public function testTransactionSuccess()
    {
        $this->assertTrue($this->pdo->beginTransaction());
        try {
            $sth = $this->pdo->prepare('insert into test(name) values (?)');
            $this->assertTrue($sth->execute(array('this is test')));
            $sth = $this->pdo->prepare('insert into test(name) values (?)');
            $this->assertTrue($sth->execute(array('this is test1')));
            $this->assertTrue($this->pdo->commit());
        } catch (DbException $e) {
            $this->pdo->rollBack();
        }
        $rows = $this->pdo->query('select * from test');
        $this->assertEquals(array(
            array('id' => 1, 'name' => 'this is test'),
            array('id' => 2, 'name' => 'this is test1'),
        ), $rows);
    }

    public function testTransactionFailure()
    {
        $this->pdo->beginTransaction();
        try {
            $sth = $this->pdo->prepare('insert into test(name) values (?)');
            $sth->execute(array('this is test'));
            $sth = $this->pdo->prepare('insert into test1(name) values (?)');
            $sth->execute(array('this is test1'));
            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->assertTrue($this->pdo->rollBack());
        } catch (DbException $e) {
            $this->assertTrue($this->pdo->rollBack());
        }
        $rows = $this->pdo->query('select * from test');
        $this->assertEquals(array(), $rows);
    }

    public function testFetch()
    {
        $this->pdo->exec('insert into test(name) values ("this is test")');
        $sth = $this->pdo->prepare('select * from test where id = ?');
        $sth->execute(array(1));
        $this->assertEquals(array('id' => 1, 'name' => 'this is test'), $this->pdo->fetch($sth));
    }

    public function testAffectedRows()
    {
        $sth = $this->pdo->prepare('insert into test(name) values (?)');
        $sth->execute(array('this is test'));
        $this->assertEquals(1, $this->pdo->affectedRows($sth));
    }

    public function testError()
    {
        try {
            $this->pdo->query('select * from test1');
        } catch (DbException $e) {
            $this->assertEquals('42S02', $this->pdo->errorCode());
            $this->assertEquals("42S02;1146;Table 'test.test1' doesn't exist", $this->pdo->errorInfo());
        }
    }
}
