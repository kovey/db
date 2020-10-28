<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-28 13:13:03
 *
 */
namespace Kovey\Db\Sql;

use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    public function testGetPrepareSqlAndBindData()
    {
        $orWhere = new Where();
        $orWhere->like('k.kovey', '%kkk%');
        $orWhere->between('f.framework', 100, 200);
        $select = new Select('test', 't');
        $select->columns(array('id' => 'user_id', 'name' => 'username', 'password', 'signature'))
               ->innerJoin(array('k' => 'kovey'), 'k.user_id=t.user_id', array('kovey' => 'kovey_framework', 'db' => 'database'))
               ->leftJoin(array('f' => 'framework'), 'f.user_id=t.user_id', array('framework' => 'framework_name', 'redis'))
               ->rightJoin(array('w' => 'websocket'), 'w.user_id=t.user_id', array('websocket' => 'websocket_name', 'rpc'))
               ->where(array('t.user_id > 1', 't.username' => 'name', 'w.websocket' => array('websocket_name', 'tcp_name', 'rpc_name')))
               ->orWhere($orWhere)
               ->having(array('f.kovey' => 'kovey_framework'))
               ->limit(20)
               ->offset(10)
               ->order('t.user_id ASC')
               ->group('user_id');
        $this->assertEquals('SELECT t.`user_id` as id,t.`username` as name,t.`password` as password,t.`signature` as signature,k.`kovey_framework` as kovey,k.`database` as db,f.`framework_name` as framework,f.`redis` as redis,w.`websocket_name` as websocket,w.`rpc` as rpc FROM `test` AS t INNER JOIN `kovey` AS k ON k.`user_id`=t.`user_id`  LEFT JOIN `framework` AS f ON f.`user_id`=t.`user_id`  RIGHT JOIN `websocket` AS w ON w.`user_id`=t.`user_id`  WHERE (t.user_id > 1 AND t.`username` = ? AND w.`websocket` IN (?,?,?)) OR (k.`kovey` LIKE ? OR f.`framework` BETWEEN ? AND ?) GROUP BY `user_id` HAVING (f.`kovey`=?) ORDER BY t.`user_id` ASC LIMIT 10,20', $select->getPrepareSql());
        $this->assertEquals(array('name', 'websocket_name', 'tcp_name', 'rpc_name', '%kkk%', 100, 200, 'kovey_framework'), $select->getBindData());
        $this->assertEquals("SELECT t.`user_id` as id,t.`username` as name,t.`password` as password,t.`signature` as signature,k.`kovey_framework` as kovey,k.`database` as db,f.`framework_name` as framework,f.`redis` as redis,w.`websocket_name` as websocket,w.`rpc` as rpc FROM `test` AS t INNER JOIN `kovey` AS k ON k.`user_id`=t.`user_id`  LEFT JOIN `framework` AS f ON f.`user_id`=t.`user_id`  RIGHT JOIN `websocket` AS w ON w.`user_id`=t.`user_id`  WHERE (t.user_id > 1 AND t.`username` = 'name' AND w.`websocket` IN ('websocket_name','tcp_name','rpc_name')) OR (k.`kovey` LIKE '%kkk%' OR f.`framework` BETWEEN '100' AND '200') GROUP BY `user_id` HAVING (f.`kovey`='kovey_framework') ORDER BY t.`user_id` ASC LIMIT 10,20", $select->toString());
    }

    public function testSelectCountSumAndSoOn()
    {
        $select = new Select('test');
        $select->columns(array('count' => 'count(1)', 'sum' => 'sum(amount)', 'u_count' => 'count(DISTINCT user_id)'));
        $this->assertEquals("SELECT count(1) as count,sum(`test`.`amount`) as sum,count(DISTINCT user_id) as u_count FROM `test`", $select->getPrepareSql());
    }
}
