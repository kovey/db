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
    private string $database = '';

    /**
     * @description server address
     *
     * @var string
     */
    private string $host = '';

    /**
     * @description user
     *
     * @var string
     */
    private string $username = '';

    /**
     * @description password
     *
     * @var string
     */
    private string $password = '';

    /**
     * @description port
     *
     * @var string
     */
    private string $port = '0';

    /**
     * @description 字符集
     *
     * @var string
     */
    private string $charset = 'uft8';

    /**
     * @description options
     *
     * @var Array
     */
    private Array $options;

    public function __construct(Array $config)
    {
        $this->options = array(
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_CASE => \PDO::CASE_LOWER,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_STRINGIFY_FETCHES => false
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

    public function getDatabase() : string
    {
        return $this->database;
    }

    public function getHost() : string
    {
        return $this->host;
    }

    public function getUser() : string
    {
        return $this->username;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function getPort() : string
    {
        return $this->port;
    }

    public function getCharset() : string
    {
        return $this->charset;
    }

    public function getOptions() : Array
    {
        return $this->options;
    }
}
