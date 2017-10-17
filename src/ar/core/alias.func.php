<?php
namespace ar\core;
use ar\core\Ar as Ar;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.base
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * component holder.
 *
 * @param string $name cname.
 *
 * @return Object
 */
function comp($name = '')
{
    return Ar::c($name);

}

/**
 * alas to cfg.
 *
 * @param string $name key of config.
 *
 * @return mixed
 */
function cfg($name = '', $default = 'NOT_RGI')
{
    if ($default === 'NOT_RGI') :
        return Ar::getConfig($name);
    else :
        return Ar::getConfig($name, $default);
    endif;

}

/**
 * route holder. \ar\core\url => url
 *
 * @param string $name    route path.
 * @param mixed  $params  route param.
 * @param string $urlMode url mode.
 *
 * @return string
 */
function url($name = '', $params = array(), $urlMode = 'NOT_INIT')
{
    return \ar\core\comp('url.route')->createUrl($name, $params, $urlMode);

}

/**
 * \ar\core\module.
 *
 * @param string $name moduleName.
 *
 * @return Module
 */
function module($name = '')
{
    static $moduleList = array();

    $name = str_replace('.', '\\', $name);
    $moduleKey = $name;

    if (!array_key_exists($moduleKey, $moduleList)) :
        if (AR_DEBUG && !AR_AS_CMD) :
            \ar\core\comp('ext.out')->deBug('|MODULE_INIT:' . $moduleKey .'|');
        endif;
        $moduleClassName = AR_ORI_NAME . '\lib\\module\\' . $name;
        $moduleList[$moduleKey] = new $moduleClassName;

        if (is_callable(array($moduleList[$moduleKey], 'initModule'))) :
            call_user_func_array(array($moduleList[$moduleKey], 'initModule'), array());
        endif;
    endif;
    if (AR_DEBUG && !AR_AS_CMD) :
        \ar\core\comp('ext.out')->deBug('|MODULE_EXEC:' . $moduleKey .'|');
    endif;
    return $moduleList[$moduleKey];

}


/**
 * filter $_GET.
 *
 * @param string $key     get key.
 * @param mixed  $default return value.
 *
 * @return mixed
 */
function get($key = '', $default = null)
{
    $getUrlParamsArray = \ar\core\comp('url.route')->parseGetUrlIntoArray();
    $ret = array();

    if (empty($key)) :
        $ret = $getUrlParamsArray;
    else :
        if (!isset($getUrlParamsArray[$key])) :
            $ret = null;
        else :
            $ret = $getUrlParamsArray[$key];
        endif;
    endif;

    $ret = \ar\core\comp('format.format')->addslashes($ret);
    if (is_numeric($ret) && $ret < 2147483647 && strlen($ret) == 1) :
        $ret = (int)$ret;
    elseif (empty($ret)) :
        $ret = $default;
    endif;

    return \ar\core\comp('format.format')->trim($ret);

}

/**
 * filter $_POST.
 *
 * @param string $key     post key.
 * @param mixed  $default return value.
 *
 * @return mixed
 */
function post($key = '', $default = null)
{
    $ret = array();

    if (empty($key)) :
        $ret = $_POST;
    else :
        if (!isset($_POST[$key])) :
            $ret = $default;
        else :
            $ret = $_POST[$key];
        endif;
    endif;

    return \ar\core\comp('format.format')->addslashes(\ar\core\comp('format.format')->trim($ret));

}

/**
 * filter $_REQUEST 有缓冲.
 *
 * @param string $key      post      key.
 * @param mixed  $default  return    value.
 * @param array  $addArray add merge array.
 *
 * @return mixed
 */
function request($key = '', $default = null, $addArray = array())
{
    static $request = array();

    if (empty($request) || !empty($addArray)) :
        if (!is_array($addArray)) :
            $addArray = array();
        endif;
        if (!AR_AS_WEB_CLI) :
            $getArr = \ar\core\get('', array());
            $postArr = \ar\core\post('', array());
            $request = array_merge($getArr, $postArr, $addArray);
        else :
            $request = $addArray;
        endif;
        $request = \ar\core\comp('format.format')->addslashes(\ar\core\comp('format.format')->trim($request));
    endif;

    if ($key) :
        if (array_key_exists($key, $request)) :
            $ret = $request[$key];
        else :
            $ret = $default;
        endif;
    else :
        $ret = $request;
    endif;

    return $ret;

}

/**
 * echo for default arEcho => dump
 *
 * @param string $echo    echo.
 * @param string $default default out.
 * @param string $key     key.
 * @param bool   $ifecho  ifecho.
 *
 * @return void
 */
function dump($echo = '', $default = '', $key = '', $ifecho = true)
{
    if (is_array($default)) :
        $index = (int)$echo;
        if (\ar\core\comp('validator.validator')->checkMutiArray($default)) :
            $echo = !empty($default[$index]) && !empty($default[$index][$key]) ? $default[$index][$key] : '';
        else :
            $echo = empty($default[$index]) ? '' : $default[$index];
        endif;
    else :
        if (empty($echo)) :
            $echo = $default;
        endif;
    endif;

    if ($ifecho) :
        echo $echo;
    else :
        return $echo;
    endif;

}

/**
 * Html segment.
 *
 * @param string  $seg     html 片段 通过 $this->assign 分配.
 * @param boolean $autoCre 自动生成布局文件.
 *
 * @return void
 */
function seg($segment, $autoCre = false)
{
    if (!is_array($segment)) :
        throw new \ar\core\Exception("segment must be an array");
    endif;

    if (empty($segment['segKey'])) :
        $keyBundle = array_keys($segment);
        $segKey = $keyBundle[0];
    else :
        $segKey = $segment['segKey'];
    endif;
    extract($segment);
    $segFile = \ar\core\cfg('DIR.SEG') . str_replace('/', DS, $segKey) . '.seg';
    if (!is_file($segFile)) :
        $segFile .= '.php';
        if (!is_file($segFile)) :
            throw new \ar\core\Exception("segment file " . $segFile . ' not found');
        endif;
    endif;

    if ($autoCre) :
        \ar\core\comp('tool.util')->copy(\ar\core\cfg('DIR.SEG') . 'Tpl' . DS . 'Public', AR_ROOT_PATH . 'Public');
        \ar\core\comp('tool.util')->copy(\ar\core\cfg('DIR.SEG') . 'Tpl' . DS . 'Layout', \ar\core\cfg('DIR.VIEW') . 'Layout');
    endif;

    extract(\ar\core\cfg('BUNDLE_VIEW_ASSIGN', array()));
    if (isset($segment['include_once']) && $segment['include_once'] == 1) :
        include_once $segFile;
    else :
        include $segFile;
    endif;

}
