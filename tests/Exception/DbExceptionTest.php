<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-22 17:23:02
 *
 */
namespace Kovey\Db\Exception;

use PHPUnit\Framework\TestCase;

class DbExceptionTest extends TestCase
{
    public function testException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('db exception');
        $e = new DbException('db exception', 'test');
        $this->assertEquals('test', $e->getCode());
        throw $e;
    }
}
