<?php
/**
 *
 * @description global uniqid
 *
 * @package     Components\Sharding
 *
 * @time        Tue Oct  1 00:47:28 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sharding;

use Kovey\Db\Sql\Update;
use Kovey\Redis\Redis;
use Kovey\Db\Mysql;

class GlobalIdentify
{
	/**
	 * @description cache
	 *
	 * @var string
	 *
	 */
	const GLOBAL_IDENTIFY_KEY = 'global_indentify_key_';

    /**
     * @description locker
     *
     * @var string
     */
    const GLOBAL_LOCKER_KEY = 'global_locker_key_';

	/**
	 * @description redis
	 *
	 * @var Kovey\Redis\Redis
	 */
	private $redis;

	/**
	 * @description mysql
	 *
	 * @var Kovey\Db\Mysql
	 */
	private $mysql;

	/**
	 * @description table
	 *
	 * @var string
	 */
	private $identifyTable;

	/**
	 * @description field
	 *
	 * @var string
	 */
	private $identifyField;

	/**
	 * @description primary key
	 *
	 * @var string
	 */
	private $primaryField;

	/**
	 * @description construct
	 *
	 * @param Kovey\Redis\Redis $redis
	 *
	 * @param Kovey\Db\Mysql $mysql
	 *
	 * @return GlobalIdentify
	 */
	public function __construct(Redis $redis, Mysql $mysql)
	{
		$this->redis = $redis;
		$this->mysql = $mysql;
	}

	/**
	 * @description set table info
	 *
	 * @param string $identifyTable
	 *
	 * @param string $identifyField
	 *
	 * @param string $primaryField
	 *
	 * @return null
	 */
	public function setTableInfo(string $identifyTable, string $identifyField, string $primaryField = 'id')
	{
		$this->identifyField = $identifyField;
		$this->identifyTable = $identifyTable;
		$this->primaryField = $primaryField;
	}

	/**
	 * @description get global indetify
	 *
	 * @return int
	 */
	public function getGlobalIdentify() : int
	{
		$id = $this->redis->rPop(self::GLOBAL_IDENTIFY_KEY . $this->identifyTable);
		if (!$id) {
            $id = $this->giveIdentifiesAgian();
		}

		return $id;
	}

	/**
	 * @description give
	 *
	 * @return bool
	 */
	private function giveIdentifiesAgian() : bool
	{
        if (!$this->lock()) {
            return false;
        }

		try {
            $row = $this->mysql->fetchRow($this->identifyTable, array($this->primaryField => 1), array($this->identifyField));
            if (!$row) {
                return false;
            }

			$up = new Update($this->identifyTable);
			$up->where(array(
				$this->primaryField => 1,
				$this->identifyField => $row[$this->identifyField]
			))
			->addSelf($this->identifyField, 2000);
			$this->mysql->update($up);
		} catch (\Exception $e) {
			return false;
        } finally {
            $this->unlock();
        }

        $id =  $row[$this->identifyField];
        go (function ($id) {
            $max = $id + 2000;
            $ids = array();
            for ($i = $id + 1; $i < $max; $i ++) {
                $ids[] = $i;
                if (count($ids) >= 100) {
                    $this->redis->lPush(self::GLOBAL_IDENTIFY_KEY . $this->identifyTable, ...$ids);
                    $ids = array();
                }
            }

            if (!empty($ids)) {
                $this->redis->lPush(self::GLOBAL_IDENTIFY_KEY . $this->identifyTable, ...$ids);
            }
        }, $id);

		return $id;
	}

    private function lock()
    {
        return $this->redis->setNx(self::GLOBAL_LOCKER_KEY . $this->identifyTable, $this->identifyTable);
    }

    public function unlock()
    {
        return $this->redis->del(self::GLOBAL_LOCKER_KEY . $this->identifyTable);
    }
}
