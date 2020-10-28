<?php
/**
 * @description
 *
 * @package
 *
 * @author zhayai
 *
 * @time 2020-10-09 20:19:35
 *
 */
namespace Kovey\Db\Adapter;

use Kovey\Db\Exception\DbException;

class Config
{
	/**
	 * @description database name
	 *
	 * @var string
	 */
    private $database;

	/**
	 * @description server address
	 *
	 * @var string
	 */
    private $host;

	/**
	 * @description user
	 *
	 * @var string
	 */
    private $username;

	/**
	 * @description password
	 *
	 * @var string
	 */
    private $password;

	/**
	 * @description port
	 *
	 * @var string
	 */
    private $port;

    /**
     * @description 字符集
     *
     * @var string
     */
    private $charset;

    /**
     * @description options
     *
     * @var Array
     */
    private $options;

    public function __construct(Array $config)
    {
        $this->options = array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_CASE => \PDO::CASE_LOWER,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        );

        foreach ($config as $field => $val) {
            if ($field === 'options') {
                if (!is_array($val)) {
                    continue;
                }

                foreach ($val as $key => $option) {
                    $this->options[$key] = $option;
                }
                continue;
            }

            $this->$field = $val;
        }
    }

    public function __set(string $name, $value)
    {
        throw new DbException("$name is unkown", 1008);
    }

    public function __get(string $name)
    {
        throw new DbException("$name is unkown", 1009);
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getUser()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getCharset()
    {
        return $this->charset;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
