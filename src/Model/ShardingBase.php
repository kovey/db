<?php
/**
 * @description sharding
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-05-09 16:07:46
 *
 */
namespace Kovey\Db\Model;

use Kovey\Db\Sql\Insert;
use Kovey\Db\Sql\Update;
use Kovey\Db\Sql\Delete;
use Kovey\Db\Sql\BatchInsert;
use Kovey\Db\DbInterface;
use Kovey\Db\Exception\DbException;
use Kovey\Db\Sharding\Database;

abstract class ShardingBase
{
	/**
	 * @description table name
	 *
	 * @var string
	 */
	protected $tableName;

    /**
     * @description database count
     *
     * @var int
     *
     */
    protected $databaseCount = 1;

	/**
	 * @description insert data
	 *
	 * @param Array $data
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function insert(Array $data, DbInterface $db, $shardingKey) : int
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$insert = new Insert($this->getTableName($shardingKey));
		foreach ($data as $key => $val) {
			$insert->$key = $val;
		}

		return $db->insert($insert);
	}

	/**
	 * @description update data
	 *
	 * @param Array $data
	 *
	 * @param Array $condition
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function update(Array $data, Array $condition, DbInterface $db, $shardingKey) : int
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$update = new Update($this->getTableName($shardingKey));
		foreach ($data as $key => $val) {
			$update->$key = $val;
		}

		$update->where($condition);
		return $db->update($update);
	}

	/**
	 * @description fetch row
	 *
	 * @param Array $condition
	 *
	 * @param Array $columns
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return Array | bool
	 *
	 * @throws DbException
	 */
	public function fetchRow(Array $condition, Array $columns, DbInterface $db, $shardingKey)
	{
		if (empty($columns)) {
			throw new DbException('selected columns is empty.', 1004); 
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		return $db->fetchRow($this->getTableName($shardingKey), $condition, $columns);
	}

	/**
	 * @description fetch all rows
	 *
	 * @param Array $condition
	 *
	 * @param Array  $columns
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return Array | false
	 *
	 * @throws Exception
	 */
	public function fetchAll(Array $condition, Array $columns, DbInterface $db, $shardingKey)
	{
		if (empty($columns)) {
			throw new DbException('selected columns is empty.', 1005); 
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		return $db->fetchAll($this->getTableName($shardingKey), $condition, $columns);
	}

	/**
	 * @description batch insert
	 *
	 * @param Array $rows
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function batchInsert(Array $rows, DbInterface $db, $shardingKey) : int
	{
		if (empty($rows)) {
			throw new DbException('rows is empty.', 1006);
		}

        $shardingKey = $this->getShardingKey($shardingKey);
		$batchInsert = new BatchInsert($this->getTableName($shardingKey));
		foreach ($rows as $row) {
			$insert = new Insert($this->getTableName($shardingKey));
			foreach ($row as $key => $val) {
				$insert->$key = $val;
			}

			$batchInsert->add($insert);
		}

		return $db->batchInsert($batchInsert);
	}

	/**
	 * @description delete
	 *
	 * @param Array $data
	 *
	 * @param Array $condition
	 *
	 * @param DbInterface $db
     *
     * @param mixed $shardingKey
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	public function delete(Array $condition, DbInterface $db, $shardingKey) : int
	{
        $shardingKey = $this->getShardingKey($shardingKey);
		$delete = new Delete($this->getTableName($shardingKey));
		$delete->where($condition);
		return $db->delete($delete);
	}

    /**
     * @description get table name
     *
     * @param int $shardingKey
     *
     * @return string
     */
    public function getTableName(int $shardingKey = -1) : string
    {
        if ($shardingKey < 0) {
            return $this->tableName;
        }

        return $this->tableName . '_' . $shardingKey;
    }

    /**
     * @description get sharding key
     *
     * @param mixed $shardingKey
     *
     * @return int
     */
    public function getShardingKey($shardingKey) : int
    {
        if (is_numeric($shardingKey) && $shardingKey < 0) {
            return $shardingKey;
        }

        $database = new Database($this->databaseCount);
        return $database->getShardingKey($shardingKey);
    }
}
