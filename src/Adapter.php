<?php
/**
 * @description Adapter
 *
 * @package Db
 *
 * @author kovey
 *
 * @time 2020-10-11 15:10:23
 *
 */
namespace Kovey\Db;

use Kovey\Db\Adapter\Config;
use Kovey\Db\Exception\DbException;

class Adapter
{
    const DB_ADAPTER_PDO = 'Pdo';

    const DB_ADAPTER_SWOOLE = 'Swoole';

    public static function factory(string $adapter, Config $config)
    {
        if (!in_array($adapter, array(self::DB_ADAPTER_SWOOLE, self::DB_ADAPTER_PDO), true)) {
            throw new DbException("$adapter is unsupport.", 1007);
        }

        try {
            $class = 'Kovey\\Db\\Adapter\\' . $adapter;
            return new $class($config);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
