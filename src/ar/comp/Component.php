<?php
namespace ar\comp;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Componnets
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * ArComponent
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Componnets
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Component
{
    // config holder
    protected $config = array();

    /**
     * initialization for component.
     *
     * @param mixed  $config config.
     * @param string $class  instanse class.
     *
     * @return Object
     */
    static public function init($config = array(), $class = __CLASS__)
    {
        $obj = new $class;
        if ($config) :
            $obj->config = $config;
        endif;
        return $obj;

    }

    /**
     * set config.
     *
     * @param mixed $config set config.
     *
     * @return void
     */
    public function setConfig($config = array())
    {
        $this->config = $config;

    }

    /**
     * get global config.
     *
     * @param string $ckey          key.
     *
     * @return mixed
     */
    public function getConfig($ckey = '')
    {
        $rt = '';
        if ($ckey) :
            if (!empty($this->config[$ckey])) :
                $rt = $this->config[$ckey];
            endif;
        else :
            $rt = $this->config;
        endif;
        return $rt;

    }

}
