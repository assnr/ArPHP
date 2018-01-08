<?php
namespace ar\core;
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
 * class class
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
class Ar
{
    // applications collections
    static private $_a = array();
    // components collections
    static private $_c = array();
    // config
    static private $_config = array();
    // autoload path
    static public $autoLoadPath;

    /**
     * init application.
     *
     * @return mixed
     */
    static public function init($loader)
    {
        require dirname(dirname(__FILE__)) . '/constant.php';

        Ar::import(AR_CORE_PATH . 'alias.func.php');

        comp('url.skeleton')->parseGlobalAson();
        $loader->add(AR_ORI_NAME, dirname(AR_ORI_PATH));

        if (AR_AS_CMD) :
            defined('AR_CMD_PATH') or define('AR_CMD_PATH', AR_ROOT_PATH . AR_DEFAULT_APP_NAME . DS);
        else :
            set_exception_handler('\\ar\\core\\Ar::exceptionHandler');
            set_error_handler('\\ar\\core\\Ar::errorHandler');
            register_shutdown_function('\\ar\\core\\Ar::shutDown');
        endif;

        if (!AR_DEBUG) :
            error_reporting(0);
        endif;

        if (AR_DEBUG && !AR_AS_CMD) :
            \ar\core\comp('ext.out')->deBug('[START]');
        endif;
        // 子项目目录
        defined('AR_PUBLIC_CONFIG_PATH') or define('AR_PUBLIC_CONFIG_PATH', AR_ROOT_PATH . 'cfg' . DS);

        if (AR_AS_WEB || AR_AS_WEB_CLI) :
            // 目录生成
            Ar::c('url.skeleton')->generate();
            // 公共配置
            if (!is_file(AR_PUBLIC_CONFIG_PATH . 'base.php')) :
                echo 'config file not found : ' . AR_PUBLIC_CONFIG_PATH . 'base.php';
                exit;
            endif;

            $globalConfig = \ar\core\comp('format.format')->arrayMergeRecursiveDistinct(
                Ar::import(AR_PUBLIC_CONFIG_PATH . 'base.php'),
                self::getConfig()
            );
            self::setConfig('', $globalConfig);

            // 引入新配置文件
            if (AR_PUBLIC_CONFIG_FILE && is_file(AR_PUBLIC_CONFIG_FILE)) :
                $otherConfig = include_once AR_PUBLIC_CONFIG_FILE;
                if (is_array($otherConfig)) :
                    Ar::setConfig('', \ar\core\comp('format.format')->arrayMergeRecursiveDistinct($otherConfig, Ar::getConfig()));
                endif;
            endif;

            // 外部工具包
            if (AR_OUTER_START) :
                return;
            else :
                // 路由解析
                Ar::c('url.route')->parse();
            endif;

            self::$_config = \ar\core\comp('format.format')->arrayMergeRecursiveDistinct(
                Ar::import(AR_CONFIG_PATH . 'default.php', true),
                self::$_config
            );

        // 命令行模式
        elseif (AR_AS_CMD) :
            // 目录生成
            Ar::c('url.skeleton')->generateCmdFile();
            self::$_config = Ar::import(AR_CMD_PATH . 'Conf' . DS . 'app.config.ini');
            self::$_config = \ar\core\comp('format.format')->arrayMergeRecursiveDistinct(
                Ar::import(AR_CMD_PATH . 'Conf' . DS . 'app.config.ini'),
                Ar::import(AR_CMD_PATH . 'Conf' . DS . 'app.config.php', true)
            );
        endif;

        \ar\core\App::run();

    }

    /**
     * set application.
     *
     * @param string $key key.
     * @param string $val key value.
     *
     * @return void
     */
    static public function setA($key, $val)
    {
        $classkey = strtolower($key);
        self::$_a[$classkey] = $val;

    }

    /**
     * get global config.
     *
     * @param string $ckey          key.
     * @param mixed  $defaultReturn default return value.
     *
     * @return mixed
     */
    static public function getConfig($ckey = '', $defaultReturn = array())
    {
        $rt = array();

        if (empty($ckey)) :
            $rt = self::$_config;
        else :
            if (strpos($ckey, '.') === false) :
                if (isset(self::$_config[$ckey])) :
                    $rt = self::$_config[$ckey];
                else :
                    if (func_num_args() > 1) :
                        $rt = $defaultReturn;
                    else :
                        $rt = null;
                    endif;
                endif;
            else :
                $cE = explode('.', $ckey);
                $rt = self::$_config;
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
     * set config.
     *
     * @param string $ckey  key.
     * @param mixed  $value value.
     *
     * @return void
     */
    static public function setConfig($ckey = '', $value = array())
    {
        if (!empty($ckey)) :
            if (strpos($ckey, '.') === false) :
                self::$_config[$ckey] = $value;
            else :
                $cE = explode('.', $ckey);
                $rt = self::$_config;
                $nowArr = array();
                $length = count($cE);
                for ($i = $length - 1; $i >= 0; $i--) :
                    if ($i == $length - 1) :
                        $nowArr = array($cE[$i] => $value);
                    else :
                        $tem = $nowArr;
                        $nowArr = array();
                        $nowArr[$cE[$i]] = $tem;
                    endif;
                endfor;
                self::$_config = \ar\core\comp('format.format')->arrayMergeRecursiveDistinct(
                    self::$_config,
                    $nowArr
                );
            endif;
        else :
            self::$_config = $value;
        endif;

    }

    /**
     * get application.
     *
     * @param string $akey key.
     *
     * @return mixed
     */
    static public function a($akey)
    {
        $akey = strtolower($akey);
        return isset(self::$_a[$akey]) ? self::$_a[$akey] : null;

    }

    /**
     * get component.
     *
     * @param string $cname component.
     *
     * @return mixed
     */
    static public function c($cname)
    {
        $cKey = strtolower($cname);

        if (!isset(self::$_c[$cKey])) :
            $config = self::getConfig('components.' . $cKey . '.config', array());
            self::setC($cKey, $config);
        endif;

        return self::$_c[$cKey];

    }

    /**
     * set component.
     *
     * @param string $component component name.
     * @param array  $config    component config.
     *
     * @return void
     */
    static public function setC($component, array $config = array())
    {
        $cKey = strtolower($component);
        if (isset(self::$_c[$cKey])) :
            return false;
        endif;

        $cArr = explode('.', $component);

        $cArr = array_map('ucfirst', $cArr);

        $className = 'ar\\comp\\' . implode($cArr, '\\');

        self::$_c[$cKey] = call_user_func_array("$className::init", array($config, $className));

    }

    /**
     * autoload register.
     *
     * @param string $class class.
     *
     * @return mixed
     */
    static public function autoLoader($class)
    {
        // $class = str_replace('\\', DS, $class);
        $posNameSpace = strrpos($class, '\\');
        if ($posNameSpace !== false) :
            // $class = substr($class, $posNameSpace + 1);
            return self::loadByNameSpace($class);
        endif;

        if (AR_OUTER_START) :
            $appModule = AR_MAN_PATH;
        else :
            $appModule = AR_ROOT_PATH . \ar\core\cfg('requestRoute.a_m', AR_DEFAULT_APP_NAME) . DS;
        endif;

        // array_push(self::$autoLoadPath, $appModule);
        array_unshift(self::$autoLoadPath, $appModule);

        if (preg_match("#[A-Z]{1}[a-z0-9]+$#", $class, $match)) :
            $appEnginePath = $appModule . $match[0] . DS;
            $extPath = $appModule . 'Ext' . DS;
            // cmd mode
            $binPath = $appModule . 'Bin' . DS;
            $protocolPath = $appModule . 'Protocol' . DS;

            // 加入Lib公共目录
            $libPath = AR_ROOT_PATH . 'Lib' . DS . $match[0] . DS;
            // lib ext
            $libExtPath = AR_ROOT_PATH . 'Lib' . DS . 'Ext' . DS;

            array_push(self::$autoLoadPath, $appEnginePath, $extPath, $binPath, $protocolPath, $libPath, $libExtPath);
        endif;
        self::$autoLoadPath = array_unique(self::$autoLoadPath);
        foreach (self::$autoLoadPath as $path) :
            $classFile = $path . $class . '.class.php';
            if (is_file($classFile)) :
                include_once $classFile;
                $rt = true;
                break;
            endif;
        endforeach;

        if (empty($rt)) :
            // 外部调用时其他框架还有其他处理 此处就忽略
            if (AR_AS_OUTER_FRAME || AR_OUTER_START) :
                return false;
            else :
                trigger_error('class : ' . $class . ' does not exist !', E_USER_ERROR);
                exit;
            endif;
        endif;

    }

    // loadByNameSpace
    static function loadByNameSpace($class)
    {
        if (strpos($class, 'ar\\') === 0) :
            $class = str_replace('ar\\', '', $class);
            $classFile = AR_FRAME_PATH . str_replace('\\', DS, $class) . '.php';
        else :
            $classFile = AR_ORI_PATH . str_replace('\\', DS, $class) . '.php';
        endif;

        if (is_file($classFile)) :
            include_once $classFile;
        else :
            trigger_error('class : ' . $class . ' does not exist !', E_USER_ERROR);
            exit;
        endif;

    }

    /**
     * set autoLoad path.
     *
     * @param string $path path.
     *
     * @return void
     */
    static public function importPath($path)
    {
        // array_push(self::$autoLoadPath, rtrim($path, DS) . DS);
        $inkey = rtrim($path, DS) . DS;
        if (in_array($inkey, self::$autoLoadPath)) :
            foreach (self::$autoLoadPath as $ink => $path) :
                if ($path == $inkey) :
                    unset(self::$autoLoadPath[$ink]);
                    break;
                endif;
            endforeach;
        endif;
        array_unshift(self::$autoLoadPath, $inkey);

    }

    /**
     * import file or path.
     *
     * @param string  $path     import path.
     * @param boolean $allowTry allow test exist.
     *
     * @return mixed
     */
    static public function import($path, $allowTry = false)
    {
        static $holdFile = array();

        if (strpos($path, DS) === false) :
            $fileName = str_replace(array('c.', 'ext.', 'app.', '.'), array('Controller.', 'Extensions.', rtrim(AR_ROOT_PATH, DS) . '.', DS), $path) . '.class.php';
        else :
            $fileName = $path;
        endif;

        if (is_file($fileName)) :
            if (substr($fileName, (strrpos($fileName, '.') + 1)) == 'ini') :
                $config = parse_ini_file($fileName, true);
                if (empty($config)) :
                    $config = array();
                endif;
                return $config;
            else :
                $file = include_once $fileName;
                if ($file === true) :
                    return $holdFile[$fileName];
                else :
                    $holdFile[$fileName] = $file;
                    return $file;
                endif;
            endif;
        else :
            if ($allowTry) :
                return array();
            else :
                throw new \ar\core\Exception('import not found file :' . $fileName);
            endif;
        endif;

    }

    // import class
    public static function importClass($className, $frompath = AR_ROOT_PATH)
    {
        $classFile = $frompath . str_replace('\\', DS, $className) . '.php';
        if (is_file($classFile)) :
            include_once $classFile;
        else :
            return false;
        endif;

    }

    /**
     * exception handler.
     *
     * @param object $e Exception.
     *
     * @return void
     */
    static public function exceptionHandler($exception)
    {
        if (get_class($exception) === '\ar\core\ServiceException') :
            \ar\core\comp('rpc.service')->response(array('error_code' => '1001', 'error_msg' => $exception->getMessage()));
            exit;
        endif;

        if (AR_DEBUG && !AR_AS_CMD) :
            echo '<div style="color: #8a6d3b;background-color: #fcf8e3;border-color: #faebcc;padding: 15px;margin-bottom: 20px;border: 1px solid transparent;border-radius: 4px">';
            echo '<b style="color:#ec8186;">' . get_class($exception) . ',code:'. $exception->getCode() .'</b> : ' . $exception->getMessage();
            echo $exception->getMessage() . '<br>';
            echo 'Stack trace:<pre>' . $exception->getTraceAsString() . '</pre>';
            echo 'thrown in <b>' . $exception->getFile() . '</b> on line <b>' . $exception->getLine() . '</b><br>';
            echo '</div>';
        endif;

    }

    /**
     * error handler.
     *
     * @param string $errno   errno.
     * @param string $errstr  error msg.
     * @param string $errfile error file.
     * @param string $errline error line.
     *
     * @return mixed
     */
    static public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (AR_RUN_AS_SERVICE_HTTP) :
            \ar\core\comp('rpc.service')->response(array('error_code' => '1011', 'error_msg' => $errstr));
            exit;
        endif;

        if (!AR_DEBUG || !(error_reporting() & $errno)) :
            return;
        endif;

        $errMsg = '';
        // 服务器级别错误
        $serverError = false;
        switch ($errno) {
        case E_USER_ERROR:
            $errMsg .= "<b style='color:red;'>ERROR</b> [$errno] $errstr<br />\n";
            $errMsg .= "  Fatal error on line $errline in file $errfile";
            $errMsg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            // header("http/1.1 404 Not Found:" . $errMsg);
            $serverError = true;
            break;

        case E_USER_WARNING:
            $errMsg .= "<b style='color:#ec8186;'>WARNING</b> [$errno] $errstr<br />\n";
            $errMsg .= " on line $errline in file $errfile <br />\n";
            break;

        case E_USER_NOTICE:
        case E_NOTICE:
            $errMsg .= "<b style='color:#ec8186;'>NOTICE</b> [$errno] $errstr<br />\n";
            $errMsg .= " on line $errline in file $errfile <br />\n";
            break;

        default:
            $errMsg .= "<b style='color:#ec8186;'>Undefined error</b> : [$errno] $errstr";
            $errMsg .= " on line $errline in file $errfile <br />\n";
            break;
        }
        if ($errMsg) :
            if (\ar\core\cfg('DEBUG_SHOW_TRACE')) :
                \ar\core\comp('ext.out')->deBug($errMsg, 'TRACE');
            else :
                if (\ar\core\cfg('DEBUG_SHOW_ERROR')) :
                    if ($serverError === true) :
                        \ar\core\comp('ext.out')->deBug($errMsg, 'SERVER_ERROR');
                    else :
                        \ar\core\comp('ext.out')->deBug($errMsg, 'ERROR');
                    endif;
                else :
                    exit($errMsg);
                endif;
            endif;
        endif;

        return true;

    }

    /**
     * shutDown function.
     *
     * @return void
     */
    public static function shutDown()
    {
        if (AR_RUN_AS_SERVICE_HTTP) :
            return;
        endif;

        if (AR_DEBUG && !AR_AS_CMD) :
            if (\ar\core\cfg('DEBUG_SHOW_EXCEPTION')) :
                \ar\core\comp('ext.out')->deBug('', 'EXCEPTION', true);
            endif;

            if (\ar\core\cfg('DEBUG_SHOW_ERROR')) :
                \ar\core\comp('ext.out')->deBug('', 'ERROR', true);
                \ar\core\comp('ext.out')->deBug('', 'SERVER_ERROR', true);
            endif;

            if (\ar\core\cfg('DEBUG_SHOW_TRACE'))  :
                \ar\core\comp('ext.out')->deBug('[SHUTDOWN]', 'TRACE', true);
            endif;
        endif;

    }

}
