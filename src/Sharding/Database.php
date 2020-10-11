<?php
/**
 *
 * @description sharding
 *
 * @package     Components\Sharding
 *
 * @time        Tue Oct  1 00:22:54 2019
 *
 * @author      kovey
 */
namespace Kovey\Db\Sharding;

class Database
{
	/**
	 * @description max count
	 *
	 * @var int
	 */
	private $maxCount;

	/**
	 * @description construct
	 *
	 * @return Database
	 */
	public function __construct($maxCount = 128)
	{
		$this->maxCount = $maxCount;
	}

	/**
	 * @description get sharding key
	 *
	 * @param mixed $id
	 *
	 * @return int
	 */
	public function getShardingKey($id) : int
	{
		if (!ctype_digit(strval($id))) {
			$id = hexdec(hash('crc32', $id));
		} else {
			$id = intval($id);
		}

		return $id % $this->maxCount;
	}
}
