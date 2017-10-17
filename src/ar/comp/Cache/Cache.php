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
 * class cache
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
abstract class Cache extends Component
{

    /**
     * cache get
     *
     * @param string $key cache key.
     *
     * @return mixed
     */
    abstract function get($key);

    /**
     * cache set.
     *
     * @param string $key   cache key.
     * @param mixed  $value value.
     *
     * @return mixed
     */
    abstract function set($key, $value);

    /**
     * cache del.
     *
     * @param string $key cache key.
     *
     * @return mixed
     */
    abstract function del($key);

    /**
     * cache flush.
     *
     * @return mixed
     */
    abstract function flush();

    /**
     * generate cache key.
     *
     * @param string $keyName keyName.
     *
     * @return string
     */
    protected function generateUniqueKey($keyName)
    {
        return md5($keyName);

    }

    /**
     * encrypt cache data.
     *
     * @param mixed $data cache date.
     *
     * @return string
     */
    protected function encrypt($data)
    {
        return serialize($data);

    }

    /**
     * decrypt cache data.
     *
     * @param mixed $data description.
     *
     * @return mixed
     */
    protected function decrypt($data)
    {
        return unserialize($data);

    }

}
