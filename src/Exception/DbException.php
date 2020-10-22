<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-11 13:04:36
 *
 */
namespace Kovey\Db\Exception;

class DbException extends \RuntimeException
{
    public function __construct(string $msg, string | int $code)
    {
        parent::__construct($msg, 0);
        $this->code = $code;
    }
}
