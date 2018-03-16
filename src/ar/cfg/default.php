<?php
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Conf
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */
return array(
    // path
    'PATH' => array(
        // Web服务器地址
        'APP_SERVER_PATH' => AR_SERVER_PATH . (!\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) ? '' : (\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) . '/')),
        'PUBLIC' => AR_SERVER_PATH . 'themes' . '/' . \ar\core\cfg('requestRoute.a_m') . '/' . \ar\core\cfg('theme') . '/',
        'GPUBLIC' => AR_ASSETS_SERVER_PATH,
    ),
    // dir
    'DIR' => array(
        // 全局缓存
        'CACHE' => AR_DATA_PATH . 'cache' . DS,
        // render path
        'RENDER' => AR_DATA_PATH . 'render' . DS,
        // log
        'LOG' => AR_DATA_PATH . 'log' . DS . (!\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) ? '' : (\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) . DS)),
        // upload
        'UPLOAD' => AR_ORI_PATH . 'upload' . DS . (!\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) ? '' : (\ar\core\cfg('requestRoute.a_m', AR_MAN_NAME) . DS)),
    ),

    // url
    'URL_MODE' => 'PATH',
    // 全局url 贪婪模式
    'URL_GREEDY' => false,

    // debug
    'DEBUG_SHOW_TRACE' => true,
    'DEBUG_SHOW_ERROR' => true,
    'DEBUG_SHOW_EXCEPTION' => true,

    // 是否调试信息到日志文件
    'DEBUG_LOG' => false,

    // 默认的模板后缀
    'TPL_SUFFIX' => 'html',

    // 路由规则
    'URL_ROUTE_RULES' => array(
        // 'index/index' => array(
        //     'mode' => 'testindex:a:/:b:', // url will be localhost/testindex123/456   a will be 123 b 456
        // ),
    ),

    'IN_NAMESPACE' => true,

);
