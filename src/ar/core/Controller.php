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
 * class \ar\core\Controller
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
class Controller
{
    // assign container
    protected $assign = array();
    // layOut file
    protected $layOutFile = 'NOT_INIT';

    protected static $serviceHandler = [];

    /**
     * init function.
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * magic function.
     *
     * @param string $name   funcName.
     * @param mixed  $params funcParames.
     *
     * @return mixed
     */
    public function __call($name, $params)
    {
        preg_match('#get(\S+)Service#', $name, $matchService);
        if (!empty($matchService[1])) :
            return service($matchService[1], $params);
        else :
            throw new \ar\core\Exception("class do not have a method $name");
        endif;
    }

    /**
     * default function.
     *
     * @param array $vals value.
     *
     * @return void
     */
    public function assign(array $vals)
    {
        foreach ($vals as $key => $val) :
            if (is_array($val) && isset($this->assign[$key]) && is_array($this->assign[$key])) :
                $this->assign[$key] = array_merge($this->assign[$key], $val);
            else :
                $this->assign[$key] = $val;
            endif;
        endforeach;

    }

    /**
     * show string function.
     *
     * @param string $ckey          key.
     * @param string $defaultReturn return.
     * @param string $show          display string.
     *
     * @return mixed
     */
    public function show($ckey = '', $defaultReturn = '', $show = true)
    {
        $rt = array();
        if (empty($ckey)) :
            $rt = $this->assign;
        else :
            if (strpos($ckey, '.') === false) :
                if (isset($this->assign[$ckey])) :
                    $rt = $this->assign[$ckey];
                endif;
            else :
                $cE = explode('.', $ckey);
                $rt = $this->assign;
                while ($k = array_shift($cE)) :
                    if (empty($rt[$k])) :
                        $rt = $defaultReturn;
                        break;
                    else :
                        $rt = $rt[$k];
                    endif;
                endwhile;
            endif;
        endif;
        if ($show) :
            echo $rt;
        else :
            return $rt;
        endif;

    }

    /**
     * display function.
     *
     * @param string  $view  view template.
     * @param boolean $fetch fetch view template.
     *
     * @return mixed
     */
    protected function display($view = '', $fetch = false)
    {
        $renderSourcePath = \ar\core\cfg('DIR.RENDER');

        if (!is_dir($renderSourcePath)) :
            mkdir($renderSourcePath);
        endif;
        $renderSourceModulePath = $renderSourcePath . \ar\core\cfg('requestRoute.a_m') . DS;
        if (!is_dir($renderSourceModulePath)) :
            mkdir($renderSourceModulePath);
        endif;
        $unikey = md5(implode('_', \ar\core\cfg('requestRoute')) . $view);
        $bodyContentTplString = '';

        $renderFile = $renderSourceModulePath . $unikey . '.aptc';

        $tplFile = \ar\core\View::getViewFileAbsoluteName($view);
        if (\ar\core\cfg('REBUILD_TPL_CACHE') || !\ar\core\View::isValidCache($tplFile, $renderFile)) :
            $renderhtmlString = $this->fetch($view, true);
            $compireReusltString = \ar\core\View::compile($renderhtmlString, $this->assign);
            file_put_contents(
                $renderFile,
                $compireReusltString
            );
        endif;

        $this->fetch($renderFile, $fetch, $this->assign);
        exit;

    }

    // fetch content from view
    public function fetch($view = '', $fetch = false, $data = array())
    {
        return \ar\core\View::fetch($view, $fetch, $data);

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirect($r = '', $show = '', $time = '0')
    {
        return \ar\core\comp('url.route')->redirect($r, $show, $time, \ar\core\cfg('SEG_REDIRECT_DEFAULT', 'default'));

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirectSuccess($r = '', $show = '', $time = '1')
    {
        return \ar\core\comp('url.route')->redirect($r, '操作成功! ' . $show, $time, \ar\core\cfg('SEG_REDIRECT_SUCCESS', 'success'));

    }

    /**
     * redirect function.
     *
     * @param mixed  $r    route.
     * @param string $show show string.
     * @param string $time time display.
     *
     * @return mixed
     */
    public function redirectError($r = '', $show = '' , $time = '4')
    {
        return \ar\core\comp('url.route')->redirect($r, '操作失败! ' . $show, $time, \ar\core\cfg('SEG_REDIRECT_ERROR', 'error'));

    }

    /**
     * show json cuccess function.
     *
     * @param string $msg     message.
     * @param string $code    code.
     * @param array  $options data.
     *
     * @return void
     */
    public function showJsonSuccess($msg = ' ', $code = '1000', array $options = array())
    {
        $this->showJson(array('ret_msg' => $msg, 'ret_code' => $code, 'error_msg' => '', 'success' => "1"), $options);

    }

    /**
     * show json error function.
     *
     * @param string $msg     message.
     * @param string $code    code.
     * @param array  $options data.
     *
     * @return void
     */
    public function showJsonError($msg = ' ', $code = '1001', array $options = array())
    {
        $this->showJson(array('ret_msg' => $msg, 'ret_code' => $code, 'error_msg' => $msg, 'success' => "0"), $options);

    }

    /**
     * json display.
     *
     * @param mixed $data    jsondata.
     * @param array $options option.
     *
     * @return mixed
     */
    public function showJson($data = array(), array $options = array())
    {
        return \ar\core\comp('ext.out')->json($data, $options);

    }

    /**
     * start controller.
     *
     * @param string $module module.
     *
     * @return void
     */
    public function runController($module)
    {
        $route = explode('/', $module);

        $requestRoute = array(
                'a_m' => \ar\core\cfg('requestRoute.a_m'),
                'a_c' => $route[0],
                'a_a' => $route[1],
            );

        Ar::a('\ar\core\ApplicationWeb')->runController($requestRoute);

    }

}
