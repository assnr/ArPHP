<?php
namespace ar\comp\Url;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;

/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * ArRoute
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Component
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Route extends Component
{
    /**
     * serverPath.
     *
     * @param string  $dir            dir.
     * @param boolean $showServerName showServerName.
     *
     * @return string
     */
    public function serverPath($dir, $showServerName = false)
    {
        $dir = str_replace(DS, '/', $dir);
        $path = dirname($_SERVER['SCRIPT_FILENAME']);
        $position = strpos($dir, $path);
        if ($position !== false) :
            $dir = AR_SERVER_PATH . trim(str_replace($path, '', $dir), '/');
        endif;
        return ($showServerName ? $this->serverName() : '') . $dir;

    }

    /**
     * pathToDir.
     *
     * @param string $path path.
     *
     * @return string
     */
    public function pathToDir($path)
    {
        if (strpos($path, '/') === 0) :
            $dir = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), DS) . DS;
            $path = trim($path, '/');
            $path = str_replace('/', DS, $path);
            $dir = $dir . $path;
        else :
            $path = str_replace('/', DS, $path);
            $dir = AR_ROOT_PATH . $path;
        endif;

        return $dir;

    }

    /**
     * host.
     *
     * @param boolean $scriptName return scriptname.
     *
     * @return string
     */
    public function host($scriptName = false)
    {
        $host = $this->serverName() . '/' . trim(str_replace(array('/', '\\', DS), '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $host = rtrim($host, '/');
        if ($scriptName) :
            $host .= '/' . basename($_SERVER['SCRIPT_NAME']);
        endif;
        return $host;

    }

    /**
     * return server name
     *
     * @return string
     */
    public function serverName()
    {
        return 'http://' . $_SERVER['HTTP_HOST'];

    }

    /**
     * parse url rules.
     *
     * @param string $url url.
     *
     * @return string
     */
    public function parseUrlForRules($url)
    {

        $phpSelf = $_SERVER['SCRIPT_NAME'];
        if (strpos($url, $phpSelf) !== false) :
            $url = str_replace($phpSelf, '', $url);
            $baseTrimUrl = $url;
        else :
            $baseTrimUrl = substr($url, strlen(AR_SERVER_PATH));
        endif;

        $foundMode = false;

        $absolutePath = ltrim($baseTrimUrl, '/');
        if (strpos($absolutePath, '?') !== false) :
            $absolutePath = substr($absolutePath, 0, strpos($absolutePath, '?'));
        endif;
        if (strpos($absolutePath, '/') === false) :
            $virtualModule = $absolutePath;
        else :
            $virtualModule = substr($absolutePath, 0, strpos($absolutePath, '/'));
        endif;

        if (!$virtualModule && \ar\core\request('a_m')) :
            $virtualModule = \ar\core\request('a_m');
        else :
            if (!in_array($virtualModule, \ar\core\cfg('moduleLists'))) :
                if (\ar\core\cfg('URL_MODE') == 'FULL') :
                    $virtualModule = \ar\core\request('a_m');
                else :
                    $virtualModule = AR_DEFAULT_APP_NAME;
                endif;
            endif;
        endif;

        // 预加载config
        $appConfigFile = AR_PUBLIC_CONFIG_PATH . $virtualModule . '.php';
        // 项目配置
        $appConfig = Ar::import($appConfigFile, true);
        if (!empty($appConfig)) :
            Ar::setConfig('', \ar\core\comp('format.format')->arrayMergeRecursiveDistinct(Ar::getConfig(), $appConfig));
        endif;
        $urlRouteRules = \ar\core\cfg('URL_ROUTE_RULES');
        if (is_array($urlRouteRules)) :
            foreach ($urlRouteRules as $key => $rules) :
                if (is_array($rules['mode'])) :
                    foreach ($rules['mode'] as $mode) :
                        if ($mode === $absolutePath) :
                            $url = AR_SERVER_PATH . $key;
                            $foundMode = true;
                            break 2;
                        endif;

                        preg_match_all('|:(.*):|U', $mode, $match);
                        if (!empty($match[1])) :
                            $mode = preg_replace('|(:.*:\(int\))|U', '(\d+)', $mode);
                            $mode = preg_replace('|(:.*:)|U', '([a-zA-z0-9%]+)', $mode);

                            $urlRegRules = '|' . $mode . '|';
                            if (preg_match_all($urlRegRules, $url, $matchRules)) :
                                $lengthOfVariable = count($match[1]);
                                for ($i = 0; $i < $lengthOfVariable; $i++) :
                                    $rulesKey = $i + 1;
                                    $_GET[$match[1][$i]] = $matchRules[$rulesKey][0];
                                endfor;
                                $url = preg_replace('|(.*)' . $mode . '(.*)|', "$1" . $key . "$" . ($lengthOfVariable + 2), $url);
                                break 2;
                            else :
                                continue;
                            endif;
                        else :
                            if (strlen($absolutePath) >= strlen($mode)) :
                                $checkRouteUrlRrefix = substr($absolutePath, 0, strlen($mode));
                                $checkRouteUrlAfter = substr($absolutePath, strlen($mode));

                                if ($checkRouteUrlRrefix == $mode) :
                                    $url = AR_SERVER_PATH . $key . $checkRouteUrlAfter;

                                    $foundMode = true;
                                    break 2;
                                endif;
                            endif;
                        endif;
                    endforeach;
                else :
                    throw new \ar\core\Exception('URL_ROUTE_RULES : "' . $key . '" mode should be an Array', 1006);
                endif;
            endforeach;
        endif;
        return $url;

    }

    /**
     * parse string.
     *
     * @return mixed
     */
    public function parse()
    {
        if (AR_AS_WEB_CLI) :
            return $this->parseCli();
        endif;

        $requestUrl = $this->parseUrlForRules($_SERVER['REQUEST_URI']);
        $phpSelf = $_SERVER['SCRIPT_NAME'];

        if (($pos = strpos($requestUrl, '?')) !== false) :
            $queryStr = substr($requestUrl, $pos + 1);
            $requestUrl = substr($requestUrl, 0, $pos);
        endif;
        if (($root = dirname($phpSelf)) != '/' && $root != '\\') :
            $requestUrl = preg_replace("#^{$root}#", '', $requestUrl);
        endif;
        $requestUrl = trim($requestUrl, '/');
        // URL 伪静态
        if (\ar\core\cfg('URL_MODE', 'PATH') == 'PATH' && \ar\core\cfg('URL_SUFFIX')) :
            if (($posSuffix = strrpos($requestUrl, '.' . \ar\core\cfg('URL_SUFFIX'))) !== false) :
                $requestUrl = substr($requestUrl, 0, $posSuffix);
            endif;
        endif;

        $pathArr = explode('/', $requestUrl);
        $temp = array_shift($pathArr);
        $m = in_array($temp, Ar::getConfig('moduleLists', array())) ? $temp : AR_DEFAULT_APP_NAME;
        $c = in_array($temp, Ar::getConfig('moduleLists', array())) ? array_shift($pathArr) : $temp;
        $a = array_shift($pathArr);
        while ($gkey = array_shift($pathArr)) :
            $_GET[$gkey] = array_shift($pathArr);
        endwhile;
        if (!empty($queryStr)) :
            parse_str($queryStr, $query);
            foreach ($_GET as $gkey => $gval) :
                if (array_key_exists($gkey, $query) && empty($query[$gkey])) :
                    unset($query[$gkey]);
                endif;
            endforeach;
            $_GET = array_merge($_GET, $query);
        endif;
        if (\ar\core\get('a_m')) :
            $m = \ar\core\get('a_m');
        endif;
        if (\ar\core\get('a_c')) :
            $c = \ar\core\get('a_c');
        endif;
        if (\ar\core\get('a_a')) :
            $a = \ar\core\get('a_a');
        endif;
        // 解析子域名 hostname
        $a_h = '';
        if (strpos($_SERVER['HTTP_HOST'], '.') !== false) :
            $serverHostArray = explode('.', $_SERVER['HTTP_HOST']);
            if (count($serverHostArray) == 3) :
                $a_h = $serverHostArray[0];
            endif;
        endif;
        // 分布式host
        $moduleLists = \ar\core\cfg('moduleLists');
        if (empty($c) && empty($a)) :
            if ($a_h && is_array($moduleLists) && in_array($a_h, $moduleLists)) :
                $m = $a_h;
            endif;
        endif;
        $requestRoute = array('a_h' => $a_h, 'a_m' => $m, 'a_c' => empty($c) ? AR_DEFAULT_CONTROLLER : $c, 'a_a' => empty($a) ? AR_DEFAULT_ACTION : $a);
        Ar::setConfig('requestRoute', $requestRoute);
        return $requestRoute;

    }

    // 处理cli参数
    public function parseCli()
    {
        global $argv;

        if (isset($argv[1])) :
            $param = $argv[1];
            $argvLength = count($argv);
            if ($argvLength > 2) :
                $validParams = array();
                for ($agi = 2; $agi < count($argv); $agi++) :
                    $argvValue = $argv[$agi];

                    if (strpos($argvValue, '=') !== false) :
                        list($vKey, $vValue) = explode('=', $argvValue);
                        $validParams[$vKey] = $vValue;
                    endif;
                endfor;
                \ar\core\request('', '', $validParams);
            endif;

        else :
            $param = '';
        endif;

        if (!$param) :
            $param = '/'. AR_DEFAULT_APP_NAME . '/' . AR_DEFAULT_CONTROLLER . '/' . AR_DEFAULT_ACTION;
        endif;

        if (substr($param, 0, 1) === '/') :
            $paramBundle = explode('/', $param);
            if (count($paramBundle) === 4) :
                $requestRoute = array(
                    'a_h' => $paramBundle[1],
                    'a_m' => $paramBundle[1],
                    'a_c' => $paramBundle[2],
                    'a_a' => $paramBundle[3],
                );
                Ar::setConfig('requestRoute', $requestRoute);
                return $requestRoute;
            else :
                exit('param format error' . PHP_EOL);
            endif;
        else :
            exit('param start must be char "/"' . PHP_EOL);
        endif;

    }

    /**
     * generate url get parame.
     *
     * @return array
     */
    public function parseGetUrlIntoArray()
    {
        static $staticMark = array(
            'firstParse' => true,
            'getUrlParamArray' => array(),
        );
        if ($staticMark['firstParse']) :
            $parseUrl = parse_url($_SERVER['REQUEST_URI']);

            if (empty($parseUrl['query'])) :

            else :
                parse_str($parseUrl['query'], $query);
                foreach ($_GET as $gkey => $gval) :
                    if (array_key_exists($gkey, $query) && empty($query[$gkey])) :
                        unset($query[$gkey]);
                    endif;
                endforeach;
                $staticMark['getUrlParamArray'] = $query;
            endif;
            $staticMark['getUrlParamArray'] = array_merge($_GET, $staticMark['getUrlParamArray']);
            $staticMark['firstParse'] = false;
        endif;
        return $staticMark['getUrlParamArray'];

    }

    /**
     * url manage.
     *
     * @param string  $urlKey      route key.
     * @param boolean $params  url get param.
     * @param string  $urlMode url mode.
     *
     * @return string
     */
    public function createUrl($urlKey = '', $params = array(), $urlMode = 'NOT_INIT')
    {
        // 路由url
        $url = $urlKey;
        // 不处理js
        if (strpos($url, 'javascript:') === 0) :
            return $url;
        endif;
        // 路由规则
        $urlRouteRules = \ar\core\cfg('URL_ROUTE_RULES');
        $defaultModule = \ar\core\cfg('requestRoute.a_m') == AR_DEFAULT_APP_NAME ? '' : \ar\core\cfg('requestRoute.a_m');
        if ($urlMode === 'NOT_INIT') :
            $urlMode = \ar\core\cfg('URL_MODE', 'PATH');
        endif;
        $prefix = rtrim(AR_SERVER_PATH . $defaultModule, '/');
        $urlParam = \ar\core\cfg('requestRoute');
        $urlParam['a_m'] = $defaultModule;

        if (isset($params['greedyUrl']) && $params['greedyUrl'] === false) :
            // do nothing
        else :
            if ((isset($params['greedyUrl']) && $params['greedyUrl'] === true) || \ar\core\cfg('URL_GREEDY') === true) :
                unset($params['greedyUrl']);
                unset($_GET['a_m']);
                unset($_GET['a_c']);
                unset($_GET['a_a']);
                // 合并参数
                if (is_array(\ar\core\get())) :
                    $getArr = \ar\core\get();
                    unset($getArr['a_m']);
                    unset($getArr['a_c']);
                    unset($getArr['a_a']);
                    $params = array_merge($getArr, $params);
                endif;
            endif;
        endif;
        // 跳转回来
        if (isset($params['ar_back']) && $params['ar_back'] === true) :
            unset($params['ar_back']);
            \ar\core\comp('list.session')->set('ar_back_url', $_SERVER['REQUEST_URI']);
        endif;
        if (empty($url)) :
            if ($urlMode == 'PATH') :
                $controller = \ar\core\cfg('requestRoute.a_c');
                $action = \ar\core\cfg('requestRoute.a_a');
                $url .= '/' . $controller . '/' . $action;
                // 后续匹配
                $urlKey = trim($url, '/');
                $url = $prefix . $url;
            endif;
        else :
            // url
            if (strpos($url, 'http') === 0) :
                $urlArr = parse_url($url);
                $reBuildUrlArr = $params;
                if (!empty($urlArr['query'])) :
                    parse_str($urlArr['query'], $urlStrArr);
                    $reBuildUrlArr = array_filter(array_merge($params, $urlStrArr));
                    $baseUrl = substr($url, 0, strpos($url, '?'));
                else :
                    $baseUrl = rtrim($url, '?');
                endif;
                $reBuildUrl = $baseUrl . '?' . http_build_query($reBuildUrlArr);
                return $reBuildUrl;
            elseif (strpos($url, '/') === false) :
                if ($urlMode != 'PATH') :
                    $urlParam['a_a'] = $url;
                else :
                    $url = $prefix . '/' . \ar\core\cfg('requestRoute.a_c') . '/' . $url;
                endif;
            elseif (strpos($url, '/') === 0) :
                if ($urlMode != 'PATH') :
                    $eP = explode('/', ltrim($url, '/'));
                    $urlParam['a_m'] = $eP[0];
                    $urlParam['a_c'] = isset($eP[1]) ? $eP[1] : null;
                    $urlParam['a_a'] = isset($eP[2]) ? $eP[2] : null;
                else :
                    $url = ltrim($url, '/');
                    $url = AR_SERVER_PATH . $url;
                endif;
            else :
                if ($urlMode != 'PATH') :
                    $eP = explode('/', $url);
                    $urlParam['a_c'] = $eP[0];
                    $urlParam['a_a'] = $eP[1];
                else :
                    $url = $prefix . '/' . $url;
                endif;
            endif;

        endif;

        if ($urlMode != 'PATH') :
            $urlParam = array_filter(array_merge($urlParam, $params));
        endif;

        // 初始化config时
        if (empty($urlMode)) :
            $urlMode = 'PATH';
        endif;
        switch ($urlMode) {

        case 'PATH' :
            if (strpos($urlKey, '/') === false) :
                $urlKey = \ar\core\cfg('requestRoute.a_c') . '/' . $urlKey;
            endif;
            // 路由解析
            if (array_key_exists($urlKey, $urlRouteRules)) :
                // 检测数组时候
                $findMode = false;
                if (is_array($urlRouteRules[$urlKey]['mode'])) :
                    foreach ($urlRouteRules[$urlKey]['mode'] as $mode) :
                        // 已寻找到模式
                        if ($findMode) :
                            break;
                        endif;
                        if (!preg_match('|:(.*):|', $mode)) :
                            $url = str_replace($urlKey, $mode, $url);
                            $findMode = true;
                            break;
                        else :
                            $tempUrl = str_replace($urlKey, $mode, $url);
                            preg_match_all('|:(.*):|U', $tempUrl, $match);
                            // 匹配的变量
                            if (!empty($match[1])) :
                                $sizeMatch = count($match[1]);
                                for ($i = 0; $i < $sizeMatch; $i++) :
                                    $variable = $match[1][$i];
                                    if (array_key_exists($variable, $params)) :
                                        $tempUrl = str_replace(':' . $variable . ':' . '(int)', $params[$variable], $tempUrl);
                                        $tempUrl = str_replace(':' . $variable . ':', $params[$variable], $tempUrl);
                                        if ($i == ($sizeMatch - 1)) :
                                            $findMode = true;
                                            $url = $tempUrl;
                                            foreach ($match[1] as $variable) :
                                                unset($params[$variable]);
                                            endforeach;
                                            break;
                                        endif;
                                    else :
                                        break;
                                    endif;
                                endfor;
                            endif;
                        endif;
                    endforeach;
                else :
                    throw new \ar\core\Exception('URL_ROUTE_RULES : "' . $urlKey . '" mode should be an Array', 1006);
                endif;
            endif;
            foreach ($params as $pkey => $pvalue) :
                if (!$pvalue && !is_numeric($pvalue)) :
                    continue;
                endif;
                $url .= '/' . $pkey . '/' . $pvalue;

            endforeach;
            if (\ar\core\cfg('URL_SUFFIX')) :
                $url = $url . '.' . \ar\core\cfg('URL_SUFFIX');
            endif;
            break;
        case 'QUERY' :
            $url = \ar\core\comp('url.route')->host() . '?' . http_build_query($urlParam);
            break;
        case 'FULL' :
            $url = \ar\core\comp('url.route')->host(true) . '?' . http_build_query($urlParam);
            break;
        }
        if (strlen($url) > 1) :
            if ($url == '//') :
                $url = '/';
            else :
                $url = rtrim($url, '/');
            endif;
        endif;
        return $url;

    }

    /**
     * redirect function.
     *
     * @param mixed  $r         route.
     * @param string $show show string.
     * @param string $time time display.
     * @param string $seg  seg  seg redirect.
     *
     * @return mixed
     */
    public function redirect($r = '', $show = '', $time = '0', $seg = '')
    {
        $show = trim($show);
        $show = preg_replace("/\n/", ' ', $show);
        if (is_string($r)) :
            $url = '';
            if (empty($r)) :
                $urlTemp = \ar\core\comp('list.session')->get('ar_back_url');
                if ($urlTemp) :
                    $url = $urlTemp;
                    \ar\core\comp('list.session')->set('ar_back_url', null);
                endif;
            else :
                if ($r == 'ar_up') :
                    if (!empty($_SERVER['HTTP_REFERER'])) :
                        $url = $_SERVER['HTTP_REFERER'];
                    endif;
                elseif (strpos($r, 'http') !== false) :
                    $url = $r;
                else :
                    $url = \ar\core\url($r);
                endif;
            endif;
        else :
            $route = empty($r[0]) ? '' : $r[0];
            $param = empty($r[1]) ? array() : $r[1];

            $url = \ar\core\comp('url.route')->createUrl($route, $param);
        endif;
        // search seg if found then render
        $redirectUrlString = <<<str
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Refresh" content="$time;URL=$url" />
</head>
<body>
</body>
</html>
str;
        if ($seg) :
            // filename
            $seg = 'Redirect/' . $seg;
            try {
                \ar\core\seg(array('segKey' => $seg, 'url' => $url, 'show' => $show, 'time' => $time));
                exit;
            } catch (\ar\core\Exception $e) {

            }
        endif;
        // header('Location: ' . $url);
        // header('Location: ' . $redirectUrl);
        exit($redirectUrlString);

    }

}
