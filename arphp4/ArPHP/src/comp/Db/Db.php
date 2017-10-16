<?php
namespace ar\comp\Db;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component.Db
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * ArDb
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Component.Db
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Db extends Component
{
    // read
    static public $readConnections = array();
    // write
    static public $writeConnections = array();
    // ArDbs
    static public $dbHolder = array();

    // connectionMark
    public $connectionMark = 'read.default';
    // pdoStatement
    protected $pdoStatement = null;

    protected $driverName = '\PDO';

    /**
     * read connection.
     *
     * @param string  $name                connection name.
     * @param boolean $returnPdoConnection return db type.
     *
     * @return void
     */
    public function read($name = 'default', $returnPdoConnection = false)
    {
        $connectionMark = 'read.' . $name;

        // 默认取第一个
        if (empty($this->config['read']['default'])) :
            $tempCk = array_keys($this->config['read']);
            $fistrKey = array_shift($tempCk);
            $this->config['read']['default'] = $this->config['read'][$fistrKey];
        endif;

        if (!isset(self::$readConnections[$name]) && isset($this->config['read'][$name])) :
            if (isset($this->config['read'][$name]['pconnect'])) :
                self::addReadConnection($name, $this->config['read'][$name]['pconnect']);
            else :
                self::addReadConnection($name, false);
            endif;
        endif;

        if (!isset(self::$readConnections[$name])) :
            throw new \ar\core\DbException('dbReadConfig not hava a param ' . $name, 1);
        endif;

        if ($returnPdoConnection) :
            return self::$readConnections[$name];
        else :
            if (!isset(self::$dbHolder[$connectionMark])) :
                $newDbHolder = clone $this;
                $newDbHolder->connectionMark = $connectionMark;
                self::$dbHolder[$connectionMark] = $newDbHolder;
            endif;
            return self::$dbHolder[$connectionMark];
        endif;

    }

    /**
     * read connection.
     *
     * @param string  $name                connection name.
     * @param boolean $returnPdoConnection return db type.
     *
     * @return mixed
     */
    public function write($name = 'default', $returnPdoConnection = false)
    {
        $this->connectionMark = 'write.' . $name;
        if (!isset(self::$writeConnections[$name]) && isset($this->config['write'][$name])) :

            if (isset($this->config['write'][$name]['pconnect'])) :
                self::addWriteConnection($name, $this->config['write'][$name]['pconnect']);
            else :
                self::addWriteConnection($name, false);
            endif;
        endif;
        if (!isset(self::$writeConnections[$name])) :
            throw new \ar\core\DbException('dbWriteConfig not hava a param ' . $name, 1);
        endif;
        if ($returnPdoConnection) :
            return self::$writeConnections[$name];
        else :
            if (!isset(self::$dbHolder[$connectionMark])) :
                $newDbHolder = clone $this;
                $newDbHolder->connectionMark = $connectionMark;
                self::$dbHolder[$connectionMark] = $newDbHolder;
            endif;
            return self::$dbHolder[$connectionMark];
        endif;

    }

    /**
     * read connection.
     *
     * @param string  $name   connection name.
     * @param boolean $update update connection data.
     *
     * @return void
     */
    protected function addConnection($mark, $update = false)
    {
        list($dataBaseType, $name) = explode('.', $mark);
        if ($dataBaseType == 'read') :
            return $this->addReadConnection($name, $update);
        else :
            return $this->addWriteConnection($name, $update);
        endif;

    }

    /**
     * read connection.
     *
     * @param string  $name   connection name.
     * @param boolean $update update connection data.
     *
     * @return void
     */
    protected function addReadConnection($name = '', $update = false)
    {
        if (!isset(self::$readConnections[$name]) || $update) :
            self::$readConnections[$name] = $this->createConnection('read.' . $name, $update);
        endif;
        return self::$readConnections[$name];

    }

    /**
     * read connection.
     *
     * @param string  $name   connection name.
     * @param boolean $update update connection data.
     *
     * @return void
     */
    protected function addWriteConnection($name = '', $update = false)
    {
        if (!isset(self::$writeConnections[$name]) || $update) :
            self::$writeConnections[$name] = $this->createConnection('write.' . $name, $update);
        endif;
        return self::$writeConnections[$name];

    }

    /**
     * read connection.
     *
     * @param string $name connection name.
     *
     * @return \PDO
     */
    protected function createConnection($name = '', $reConnect = false)
    {
        list($dataBaseType, $mark) = explode('.', $name);
        $dsn = $this->config[$dataBaseType][$mark]['dsn'];
        $user = $this->config[$dataBaseType][$mark]['user'];
        $pass = $this->config[$dataBaseType][$mark]['pass'];
        $option = $this->config[$dataBaseType][$mark]['option'];
        try {
            $driverClassName = $this->driverName;
            return new $driverClassName($dsn, $user, $pass, $option);

        } catch (\PDOException $e) {
            if ($reConnect === true) :
                // [2002] server has gone away
                if ($e->getCode() == 2002) :
                    sleep(1);
                    return $this->createConnection($name, true);
                endif;
                /*
                if ((strpos($e->getMessage(), 'Lost connection to MySQL server') !== false) || (strpos($e->getMessage(), 'server has gone away') !== false)) :
                    sleep(1);
                    return $this->createConnection($name, true);
                endif;
                */
            endif;
            throw $e;

        }

    }

    /**
     * config key
     *
     * @param string $configKey.
     *
     * @return String
     */
    protected function getCurrentConfig($configKey = '')
    {
        list($dataBaseType, $mark) = explode('.', $this->connectionMark);

        if (empty($this->config[$dataBaseType][$mark])) :
            throw new \ar\core\DbException("Db Config Mark Error : " . $this->connectionMark . ' Required');
        endif;
        if (empty($configKey)) :
            return $this->config[$dataBaseType][$mark];
        else :
            if (array_key_exists($configKey, $this->config[$dataBaseType][$mark])) :
                return $this->config[$dataBaseType][$mark][$configKey];
            else :
                throw new \ar\core\DbException("Db Config Lost Key Error : " . $configKey . ' Required');
            endif;
        endif;


    }

    /**
     * pdo connection.
     *
     * @return \PDO
     */
    protected function getDbConnection()
    {
        if (empty($this->connectionMark) || !strpos($this->connectionMark, '.')) :
            throw new \ar\core\DbException("Connection Mark Error : " . $this->connectionMark);
        endif;
        list($dataBaseType, $mark) = explode('.', $this->connectionMark);
        switch ($dataBaseType) {
            case 'read':
                return $this->read($mark, true);
                break;
            case 'write':
                return $this->write($mark, true);
                break;

            default:
                throw new \ar\core\DbException("Connection Mark DataBase Type Error : " . $this->connectionMark);
                break;

        }

    }

    /**
     * read connection.
     *
     * @param string $attribute attr.
     * @param string $value     value.
     *
     * @return Object
     */
    public function setPdoAttributes($attribute , $value = '')
    {
        $this->getDbConnection()->setAttribute($attribute, $value);
        return $this;

    }

    /**
     * trans.
     *
     * @return boolean
     */
    public function transBegin()
    {
        return $this->getDbConnection()->beginTransaction();

    }

    /**
     * trans commit.
     *
     * @return boolean
     */
    public function transCommit()
    {
        return $this->getDbConnection()->commit();

    }

    /**
     * trans roolbank.
     *
     * @return boolean
     */
    public function transRollBack()
    {
        return $this->getDbConnection()->rollBack();

    }

    /**
     * if in trans.
     *
     * @return boolean
     */
    public function inTransaction()
    {
        // This method actually seems to work fine on PHP5.3.5 (and probably a few older versions).
        return $this->getDbConnection()->inTransaction();

    }

    // clone objects
    public function __clone()
    {
        $this->connectionMark = '';

    }

}
