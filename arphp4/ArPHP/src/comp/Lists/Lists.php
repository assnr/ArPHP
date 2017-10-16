<?php
namespace ar\comp\Lists;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component.List
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * Core.Component.List
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
class Lists extends Component
{
    // container
    protected $c = array();

    /**
     * if contains.
     *
     * @param string $key key.
     *
     * @return boolean
     */
    public function contains($key)
    {
        return isset($this->c[$key]);

    }

    /**
     * set.
     *
     * @param string $key   key.
     * @param mixed  $value value.
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->c[$key] = $value;

    }

    /**
     * get.
     *
     * @param string $key key.
     *
     * @return mixed
     */
    public function get($ckey, $defaultReturn = null)
    {
        $rt = null;

        if (empty($ckey)) :
            $rt = $this->c;
        else :
            if (strpos($ckey, '.') === false) :
                if (isset($this->c[$ckey])) :
                    $rt = $this->c[$ckey];
                else :
                    if (func_num_args() > 1) :
                        $rt = $defaultReturn;
                    else :
                        $rt = null;
                    endif;
                endif;
            else :
                $cE = explode('.', $ckey);
                $rt = $this->c;
                // 0 判断
                while (($k = array_shift($cE)) || is_numeric($k)) :
                    if (!isset($rt[$k])) :
                        if (func_num_args() > 1) :
                            $rt = $defaultReturn;
                        else :
                            $rt = null;
                        endif;
                        break;
                    else :
                        $rt = $rt[$k];
                    endif;
                endwhile;
            endif;

        endif;

        return $rt;

    }

    /**
     * flush.
     *
     * @return mixed
     */
    public function flush()
    {
        $this->c = array();

    }

}
