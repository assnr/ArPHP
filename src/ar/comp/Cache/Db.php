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
 * class db cache
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
class Db extends Cache
{
    // cache path
    public $cacheTableName;

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

        $obj->cacheTableName = empty($obj->config['cacheTableName']) ? 'coop_db_cache' : $obj->config['cacheTableName'];

        return $obj;

    }

    /**
     * get cache file name.
     *
     * @param string $key cache key.
     *
     * @return string
     */
    public function cacheFile($key)
    {
        return \ar\core\comp('db.mysql')->table($this->cacheTableName)->where(['key' => $key])->queryRow();

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
        $cacheFile = $this->cacheFile($key);

        if (is_array($cacheFile)) :
            if ($this->checkExpire($cacheFile)) :
                $data = null;
                $this->del($key);
                $data = null;
            else :
                $data = $this->decrypt($cacheFile['val']);
            endif;
        else :
            $data = null;
        endif;

        return $data;

    }

    /**
     * cache set.
     *
     * @param string  $key    cache key.
     * @param mixed   $value  value.
     * @param integer $expire time.
     *
     * @return mixed
     */
    public function set($key, $value, $expire = 0)
    {
        if ($expire == 0) :
            // 99年过期
            $timeExpire = time() + 3600 * 24 * 365 * 99;
        else :
            $timeExpire = time() + $expire;
        endif;

        if (\ar\core\comp('db.mysql')->table($this->cacheTableName)->where(['key' => $key])->count() > 0) {
            return \ar\core\comp('db.mysql')->table($this->cacheTableName)
                ->where(['key' => $key])
                ->update(['val' => $this->encrypt($value), 'expire' => $timeExpire]);
        } else {
            return \ar\core\comp('db.mysql')->table($this->cacheTableName)
                ->insert(['key' => $key, 'val' => $this->encrypt($value), 'expire' => $timeExpire]);
        }
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
        return \ar\core\comp('db.mysql')->table($this->cacheTableName)->where(['key' => $key])->delete();
    }

    /**
     * check cache valid.
     *
     * @param string $file file.
     *
     * @return boolean
     */
    public function checkExpire($file)
    {
        $timeExpire = $file['expire'];
        return $timeExpire == 0 ? false : ($timeExpire < time());

    }

    /**
     * cache flush.
     *
     * @param boolean $force cleal all cache.
     * @param string  $dir   cleal all cache.
     *
     * @return mixed
     */
    public function flush($force = false, $dir = '')
    {
        return \ar\core\comp('db.mysql')->table($this->cacheTableName)->where(['id' => 0])->delete();
    }

    /**
     * cache flush all.
     *
     * @param boolean $force cleal all cache.
     * @param array   $dir   array module.
     *
     * @return mixed
     */
    public function flushAll($force = true, $module = array())
    {
        return \ar\core\comp('db.mysql')->table($this->cacheTableName)->where(['id' => 0])->delete();
    }
}
