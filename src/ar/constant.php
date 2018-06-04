<?php
// version
define('AR_VERSION', '5.1.9');
// 启动时间
defined('AR_START_TIME') or define('AR_START_TIME', microtime(true));
// // 开启调试 是
// defined('AR_DEBUG') or define('AR_DEBUG', true);
// // 外部启动 否 默认管理目录ArMan
// defined('AR_OUTER_START') or define('AR_OUTER_START', false);
// // 自启动session
// defined('AR_AUTO_START_SESSION') or define('AR_AUTO_START_SESSION', true);
// // 作为外部框架加载 可嵌入其他框架
// defined('AR_AS_OUTER_FRAME') or define('AR_AS_OUTER_FRAME', false);
// // 内部实现http webservice 多套 arphp程序互调接口
// defined('AR_RUN_AS_SERVICE_HTTP') or define('AR_RUN_AS_SERVICE_HTTP', false);
// // 实现 cmd socket 编程
// defined('AR_AS_CMD') or define('AR_AS_CMD', false);
// // web application 默认方式
// defined('AR_AS_WEB') or define('AR_AS_WEB', true);
// // web cli 模式
// defined('AR_AS_WEB_CLI') or define('AR_AS_WEB_CLI', false);
// // app名 main
// defined('AR_DEFAULT_APP_NAME') or define('AR_DEFAULT_APP_NAME', 'main');
// // 默认的控制器名
// defined('AR_DEFAULT_CONTROLLER') or define('AR_DEFAULT_CONTROLLER', 'Index');
// // 默认的Action
// defined('AR_DEFAULT_ACTION') or define('AR_DEFAULT_ACTION', 'index');
// 目录分割符号
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
// ar框架目录
defined('AR_FRAME_PATH') or define('AR_FRAME_PATH', dirname(__FILE__) . DS);
// 项目根目录
defined('AR_ROOT_PATH') or define('AR_ROOT_PATH', realpath(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . DS);
// 核心目录
defined('AR_CORE_PATH') or define('AR_CORE_PATH', AR_FRAME_PATH . 'core' . DS);
// 配置目录
defined('AR_CONFIG_PATH') or define('AR_CONFIG_PATH', AR_FRAME_PATH . 'cfg' . DS);

// 服务地址
defined('AR_SERVER_PATH') or define('AR_SERVER_PATH', ($dir = dirname($_SERVER['SCRIPT_NAME'])) == DS ? '/' : str_replace(DS, '/', $dir) . '/');

define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT',    3);
define('HDOM_TYPE_ENDTAG',  4);
define('HDOM_TYPE_ROOT',    5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO',     3);
define('HDOM_INFO_BEGIN',   0);
define('HDOM_INFO_END',     1);
define('HDOM_INFO_QUOTE',   2);
define('HDOM_INFO_SPACE',   3);
define('HDOM_INFO_TEXT',    4);
define('HDOM_INFO_INNER',   5);
define('HDOM_INFO_OUTER',   6);
define('HDOM_INFO_ENDSPACE',7);
define('DEFAULT_TARGET_CHARSET', 'UTF-8');
define('DEFAULT_BR_TEXT', "\r\n");
define('DEFAULT_SPAN_TEXT', " ");

if (!defined('MAX_FILE_SIZE'))
{
    define('MAX_FILE_SIZE', 600000);
}
