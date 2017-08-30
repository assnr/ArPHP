<?php
namespace ar\comp\Cache;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Components.Cache
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * class redis cache
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.base
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Redis extends Cache
{
    // object redis
    private $_redis = null;

    /**
     * initialization function.
     *
     * @param mixed  $config config.
     * @param string $class  hold class.
     *
     * @return Object
     */
    static public function init($config = array(), $class = __CLASS__)
    {
        $obj = parent::init($config, $class);
        $obj->connect();
        return $obj;

    }

    /**
     * redis connect.
     *
     * @return mixed
     */
    public function connect($reConnect = false)
    {
        if (!$this->_redis || $reConnect) :
            $this->_redis = new Redis();
            if (isset($this->config['pconnect']) && $this->config['pconnect'] === true) :
                $this->_redis->pconnect($this->config['host'], $this->config['port'], 2);
            else :
                $this->_redis->connect($this->config['host'], $this->config['port'], 2);
            endif;
            if (!$this->_redis->IsConnected() && !$reConnect) :
                throw new \ar\core\Exception('Failed to connect to redis: redis server went away');
            else :
                if(!empty($this->config['password'])) :
                    $this->_redis->auth($this->config['password']);
                endif;
                if (!empty($this->config['db'])) :
                    $this->_redis->select($this->config['db']);
                endif;
            endif;
        endif;

    }

    // 断线重连
    public function reConnect()
    {
        $this->connect(true);
        sleep(1);

    }

    //存放被锁的标志名的数组
    private $lockedNames = array();

    /**
     * 加锁
     *
     * @param string 锁的标识名
     * @param int 获取锁失败时的等待超时时间(秒), 在此时间之内会一直尝试获取锁直到超时. 为 0 表示失败后直接返回不等待
     * @param int 当前锁的最大生存时间(秒), 必须大于 0 . 如果超过生存时间后锁仍未被释放, 则系统会自动将其强制释放
     * @param int 获取锁失败后挂起再试的时间间隔(微秒)
     */
    public function lock($name, $timeout = 0, $expire = 6, $waitIntervalUs = 100000)
    {
        if(empty($name)) return false;

        $timeout = (int)$timeout;
        $expire = max((int)$expire, 5);

        $redisKey = "Lock:$name";
        while (true) {
            $now = microtime(true);
            $timeoutAt = $now + $timeout;
            $expireAt = $now + $expire;
            $result = $this->redis()->setnx($redisKey, (string)$expireAt);
            if ($result !== false) :
                //对$redisKey设置生存时间
                $this->redis()->expire($redisKey, $expire);
                //将最大生存时刻记录在一个数组里面
                $this->lockedNames[$name] = $expireAt;
                return true;
            endif;
            //以秒为单位，返回$redisKey 的剩余生存时间
            $ttl = $this->redis()->ttl($redisKey);
            // TTL 小于 0 表示 key 上没有设置生存时间(key 不会不存在, 因为前面 setnx 会自动创建)
            // 如果出现这种情况, 那就是进程在某个实例 setnx 成功后 crash 导致紧跟着的 expire 没有被调用. 这时可以直接设置 expire 并把锁纳为己用
            if ($ttl < 0) :
                $this->redis()->set($redisKey, (string)$expireAt, $expire);
                $this->lockedNames[$name] = $expireAt;
                return true;
            endif;
            // 设置了不等待或者已超时
            if($timeout <= 0 || microtime(true) > $timeoutAt) break;
            // 挂起一段时间再试
            usleep($waitIntervalUs);

        }
        return false;

    }

    /**
     * 给当前锁增加指定的生存时间(秒), 必须大于 0
     *
     * @param string 锁的标识名
     * @param int 生存时间(秒), 必须大于 0
     */
    public function expire($name, $expire)
    {
        if ($this->isLocking($name)) :
            if ($this->redis()->expire("Lock:$name", max($expire, 1))) :
                return true;
            endif;
        endif;
        return false;

    }

    /**
     * 判断当前是否拥有指定名称的锁
     *
     * @param mixed $name
     */
    public function isLocking($name) {
        if (isset($this->lockedNames[$name])) :
             return (string)$this->lockedNames[$name] == (string)$this->redis()->get("Lock:$name");
        endif;
        return false;

    }

    /**
     * 释放锁
     *
     * @param string 锁的标识名
     */
    public function unlock($name)
    {
        if ($this->isLocking($name)) :
            if ($this->redis()->del("Lock:$name")) :
                unset($this->lockedNames[$name]);
                return true;
            endif;
        endif;
        return false;

    }

    /** 释放当前已经获取到的所有锁 */
    public function unlockAll()
    {
        $allSuccess = true;
        foreach ($this->lockedNames as $name => $item) :
            if (false === $this->unlock($name)) :
                $allSuccess = false;
            endif;
        endforeach;
        return $allSuccess;

    }

    // return redis instance
    public function redis()
    {
        // if (!empty($this->config['pconnect'])) :
        while ($this->_redis->IsConnected() === false) :
            echo 'c false';
            $this->reConnect();
        endwhile;
        // endif;
        return $this->_redis;

    }

    // get key
    public function generateUniqueKey($key)
    {
        return $key;

    }

    /**
     * cache get
     *
     * @param string $key cache key.
     *
     * @return mixed
     */
    public function get($key)
    {
        try {
            $value = $this->redis()->get($this->generateUniqueKey($key));
            if (is_scalar($value)) :
                // 数组
                if (strpos($value, '[') === 0 || strpos($value, '{') === 0) :
                    return json_decode($value, true);
                else :
                    return $value;
                endif;
            else :
                return false;
            endif;
        } catch (Exception $e) {
            \ar\core\comp('list.log')->record($key, 'rsexception:' . __FUNCTION__);
            return null;
        }

    }

    /**
     * cache set.
     *
     * @param string $key   cache key.
     * @param mixed  $value value.
     *
     * @return mixed
     */
    public function set($key, $value, $expire = 0)
    {
        if (is_array($value)) :
            $value = json_encode($value);
        endif;
        if ($expire > 0) :
            return (bool)$this->redis()->set($this->generateUniqueKey($key), $value, $expire);
        else :
            return (bool)$this->redis()->set($this->generateUniqueKey($key), $value);
        endif;

    }

    // increate a key
    public function incr($key, $start = 0)
    {
        if ($start > 0) :
            return $this->redis()->incrBy($this->generateUniqueKey($key), $start);
        else :
            return $this->redis()->incr($this->generateUniqueKey($key));
        endif;

    }

    // exists
    public function exists($key)
    {
        return $this->redis()->exists($this->generateUniqueKey($key));

    }

    /**
     * cache del.
     *
     * @param string $key cache key.
     *
     * @return mixed
     */
    public function del($key)
    {
        return (bool)$this->redis()->del($this->generateUniqueKey($key));

    }

    /**
     * cache flush.
     *
     * @return mixed
     */
    public function flush()
    {
        return $this->redis()->flushDB();

    }

    /**
     * cache flush all db.
     *
     * @return mixed
     */
    public function flushAll()
    {
        return $this->redis()->flushAll();

    }

}
