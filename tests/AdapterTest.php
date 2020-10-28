<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-28 09:41:53
 *
 */
namespace Kovey\Db;

use PHPUnit\Framework\TestCase;
use Kovey\Db\Adapter\Pdo;
use Kovey\Db\Adapter\Swoole;
use Kovey\Db\Adapter\Config;
use Kovey\Db\Exception\DbException;

class AdapterTest extends TestCase
{
    public function testFactoryPdo()
    {
        $pdo = Adapter::factory(Adapter::DB_ADAPTER_PDO, new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));

        $this->assertInstanceOf(AdapterInterface::class, $pdo);
        $this->assertInstanceOf(Pdo::class , $pdo);
        $this->assertTrue($pdo->connect());
    }

    public function testFactorySwoole()
    {
        $swoole = Adapter::factory(Adapter::DB_ADAPTER_SWOOLE, new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));

        $this->assertInstanceOf(AdapterInterface::class, $swoole);
        $this->assertInstanceOf(Swoole::class , $swoole);
        $this->assertTrue($swoole->connect());
    }

    public function testFactoryFailure()
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('other is unsupport.');
        $other = Adapter::factory('other', new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'port' => 3306,
            'charset' => 'UTF8',
            'options' => array()
        )));
    }
}
