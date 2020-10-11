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

class Adapter
{
    public static function factory(string $adapter, Config $config)
    {
        try {
            $class = 'Kovey\\Db\\Adapter\\' . ucfirst($adapter);
            return new $class($config);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
