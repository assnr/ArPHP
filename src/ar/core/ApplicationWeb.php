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
 * application
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
class ApplicationWeb extends Application
{
    // route container
    public $route = array();

    /**
     * start function.
     *
     * @return void
     */
    public function start()
    {
        if (AR_DEBUG && !AR_AS_CMD) :
            \ar\core\comp('ext.out')->deBug('[APP_WEB_START]');
        endif;
        if (AR_AUTO_START_SESSION && ini_get('session.auto_start') == 0) :
            if (!AR_AS_WEB_CLI) :
                session_start();
            endif;
        endif;

        $this->processRequest();

    }

    /**
     * process.
     *
     * @return void
     */
    public function processRequest()
    {
        $this->runController(Ar::getConfig('requestRoute'));

    }

    /**
     * default controller.
     *
     * @param string $route route.
     *
     * @return mixed
     */
    public function runController($route)
    {
        if (AR_DEBUG && !AR_AS_CMD) :
            \ar\core\comp('ext.out')->deBug('[CONTROLLER_RUN]');
        endif;

        Ar::setConfig('requestRoute', $route);

        if (empty($route['a_c'])) :
            $c = 'Index';
        else :
            $c = ucfirst($route['a_c']);
        endif;

        $this->route['a_c'] = $c;
        // $class = $c . 'Controller';
        $class = $c;

        if (AR_DEBUG && !AR_AS_CMD) :
            \ar\core\comp('ext.out')->deBug('|CONTROLLER_EXEC:'. $class .'|');
        endif;

        $class = AR_ORI_NAME . '\\ctl\\' . $route['a_m'] . '\\' . $class;

        if (class_exists($class)) :
            $this->_c = new $class;
            $action = ($a = empty($route['a_a']) ? AR_DEFAULT_ACTION : $route['a_a']);
            $this->route['a_a'] = $a;
            switch ($action) {
                case 'init':
                    throw new \ar\core\Exception('Action: ' . $action . ' not found');
                    break;
                // case '':
                default:
                    # code...
                    break;
            }

            $preAction = $action;

            $parentClassName = get_parent_class($this->_c);
            while ($parentClassName !== 'ar\core\Controller' && $parentClassName !== 'ar\core\ApiController') {
                $parentClassName = get_parent_class($parentClassName);
                if (!$parentClassName) {
                    break;
                }
            }
            if ($parentClassName === 'ar\core\ApiController') :
                if (\ar\core\comp('tools.util')->isGet()) :
                    $this->_c->request = \ar\core\get();
                    $action = 'get_' . $action;
                elseif (\ar\core\comp('tools.util')->isPost()) :
                    $this->_c->request = \ar\core\post();
                    $action = 'post_' . $action;
                elseif (\ar\core\comp('tools.util')->isPut()) :
                    $this->_c->request = \ar\core\request();
                    $action = 'put_' . $action;
                endif;

                if (!method_exists($this->_c, $action)) :
                    $action = $preAction;
                    if (!method_exists($this->_c, $action)) :
                        $errorMsg = 'Action: ' . $action . ' not found';
                        $this->_c->handleError($errorMsg);
                    else :
                        $this->_c->request = \ar\core\request();
                    endif;
                endif;

                try {
                    if (!$this->_c->request) :
                        $this->_c->request = [];
                    endif;
                    $this->_c->init();
                    $reflection = new \ReflectionMethod($this->_c, $action);
                    $pass = array();
                    foreach($reflection->getParameters() as $param) :
                        if(isset($this->_c->request[$param->getName()])) :
                          $pass[] = $this->_c->request[$param->getName()];
                        else :
                            try {
                                $pass[] = $param->getDefaultValue();
                            } catch (\ReflectionException $e) {
                                throw new \ar\core\Exception('param "' . $param->getName() . '" is missing ', 2010);
                            }
                        endif;
                    endforeach;
                    call_user_func_array(array($this->_c, $action), $pass);
                } catch (\ar\core\Exception $e) {
                    $this->_c->handleError($e->getMessage());
                }

            else :
                if (!method_exists($this->_c, $action)) :
                    throw new \ar\core\Exception('Action: ' . $action . ' not found');
                endif;

                $this->_c->init();

                if (is_callable(array($this->_c, $action))) :
                    try {
                        if (AR_DEBUG && !AR_AS_CMD) :
                            \ar\core\comp('ext.out')->deBug('|ACTION_RUN:' . $action . '|');
                        endif;
                        $this->_c->$action();
                        if (AR_AS_OUTER_FRAME) :
                            exit;
                        endif;
                    } catch (\ar\core\Exception $e) {
                        if (!AR_AS_OUTER_FRAME) :
                            throw new \ar\core\Exception($e->getMessage());
                        endif;
                    }
                else :
                    if (!AR_AS_OUTER_FRAME) :
                        throw new \ar\core\Exception('Action ' . $action . ' not found');
                    endif;
                endif;
            endif;
        else :
            throw new \ar\core\Exception('Controller:' . $class . ' not found');
        endif;

    }

}
