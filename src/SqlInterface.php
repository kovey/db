<?php
/**
 *
 * @description SQL interface
 *
 * @package     Kovey\Db
 *
 * @time        Tue Sep 24 09:02:25 2019
 *
 * @author      kovey
 */
namespace Kovey\Db;

interface SqlInterface
{
    /**
     * @description get prepare sql
     *
     * @return string | null
     */
    public function getPrepareSql() : ? string;

    /**
     * @description get bind data
     *
     * @return Array
     */
    public function getBindData() : Array;

    /**
     * @description format sql
     *
     * @return string
     */
    public function toString() : string;
}
