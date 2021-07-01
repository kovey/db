<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2021-07-01 14:19:05
 *
 */
namespace Kovey\Db\ForUpdate;

class Type
{
    const FOR_UPDATE_NO = 'NO';

    const FOR_UPDATE_NORMAL = 'NORMAL';

    const FOR_UPDATE_NOWAIT = 'NOWAIT';

    const SQL_FOR_UPDATE = ' FOR UPDATE';

    const SQL_FOR_UPDATE_NOWAIT = ' FOR UPDATE NOWAIT';

    public static function getForUpdateSql(string $type) : string
    {
        return match($type) {
            self::FOR_UPDATE_NO => '',
            self::FOR_UPDATE_NORMAL => self::SQL_FOR_UPDATE,
            self::FOR_UPDATE_NOWAIT => self::SQL_FOR_UPDATE_NOWAIT,
            default => ''
        };
    }
}
