<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-21 17:39:38
 *
 */
Swoole\Coroutine\Run(function () {
    try {
        global $argc, $argv;
        require __DIR__ . '/../vendor/bin/phpunit';
    } catch (Exception $e) {
        if ($e->getMessage() === 'swoole exit') {
            return;
        }

        throw $e;
    }
});
