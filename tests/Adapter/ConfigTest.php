<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-21 12:04:39
 *
 */
namespace Kovey\Db\Adapter;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfig()
    {
        $config = new Config(array(
            'database' => 'test',
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'password',
            'port' => 3306,
            'charset' => 'utf-8',
            'options' => array()
        ));
        $this->assertEquals('test', $config->getDatabase());
        $this->assertEquals('127.0.0.1', $config->getHost());
        $this->assertEquals('root', $config->getUser());
        $this->assertEquals('password', $config->getPassword());
        $this->assertEquals(3306, $config->getPort());
        $this->assertEquals('utf-8', $config->getCharset());
        $this->assertEquals(array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_CASE => \PDO::CASE_LOWER,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ), $config->getOptions());
    }
}
