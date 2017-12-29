<?php
namespace ar\comp\Url;
use ar\core\Ar as Ar;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component.Url
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * default app generate
 *
 * default hash comment :
 *
 * <code>
 *  # This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Component.Url
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Skeleton extends \ar\comp\Component
{
    // appName
    public $appName = '';
    // basepath of app
    protected $basePath = '';

    /**
     * generator.
     *
     * @return mixed
     */
    public function generateFolders()
    {
        $folderLists = array(
            $this->basePath,
            AR_PUBLIC_CONFIG_PATH,
            $this->basePath . 'assets',
            $this->basePath . 'assets/img',
            $this->basePath . 'assets/css',
            $this->basePath . 'assets/js',

            $this->basePath . 'ctl',
            $this->basePath . 'ctl/main',
            $this->basePath . 'ctl/main/service',

            $this->basePath . 'lib',
            $this->basePath . 'lib/model',
            $this->basePath . 'lib/module',
            $this->basePath . 'lib/ext',

            $this->basePath . 'themes',
            $this->basePath . 'themes/main',
            $this->basePath . 'themes/main/def',
            $this->basePath . 'themes/main/def/css',
            $this->basePath . 'themes/main/def/js',
            $this->basePath . 'themes/main/def/img',

            $this->basePath . 'view',
            $this->basePath . 'view/main',
            $this->basePath . 'view/main/def',
            $this->basePath . 'view/main/def/Index',
            $this->basePath . 'view/main/def/Layout',
        );

        foreach($folderLists as $folder) :
            if (!$this->check($folder)) :
                if (!@mkdir($folder)) :
                    throw new \ar\core\Exception("folder $folder create failed !");
                endif;
            endif;
        endforeach;

    }

    /**
     * files.
     *
     * @return mixed
     */
    public function generateFiles()
    {
        $fileLists = array(
            $this->basePath . 'ctl/main/Index.php' => '<?php
/**
 * Powerd by ArPHP.
 *
 * default Controller.
 *
 */
namespace ' . AR_ORI_NAME . '\ctl\main;
use \ar\core\Controller as Controller;
/**
 * Default Controller of webapp.
 */
class Index extends Controller
{
    /**
     * just the example of get contents.
     *
     * @return void
     */
    public function index()
    {
        // 调用本地服务组件
        // $this->getTestService()->myTestFunc();
        $this->assign(["welcomeTitle" => "hello arphp"]);
        $this->display();

    }

}
',
  $this->basePath . 'ctl/main/service/Test.php' => '<?php
/**
 * Powerd by ArPHP.
 *
 * test service.
 *
 */
namespace ' . AR_ORI_NAME . '\ctl\main\service;
/**
 * Default Controller of webapp.
 */
class Test
{
    // in controller $this->getTestService()->myTestFunc();
    public function myTestFunc()
    {
        echo "my test func is called";

    }

}

',

        $this->basePath . 'view/main/def/Index/index.html' => '<import from="/Layout/global" name="html5">
    <extend name="title">
          {{welcomeTitle}},  this is html title
    </extend>
    <extend name="body">
        <h1>
            {{welcomeTitle}}, version: {{constant("AR_VERSION")}}
        </h1>
    </extend>
</import>
',
$this->basePath . 'view/main/def/Layout/global.html' => '<export name="html5">
<html>
    <head>
        <title>
            <extend name="title"/>
        </title>
        <extend name="css"/>
        <extend name="jshead"/>
    </head>
    <body>
        <extend name="body"/>
    </body>
    <extend name="jsfoot"/>
</html>
</export>
',
        AR_PUBLIC_CONFIG_PATH . 'main.php' => '<?php
/**
 * Ar default app config file.
 *
 * @author ycassnr <ycassnr@gmail.com>
 */
return array(
);',

        AR_PUBLIC_CONFIG_PATH . 'base.php' => '<?php
/**
 * Ar default public config file.
 *
 */
return array(
    // 组件配置
    \'components\' => array(
        // 依赖懒加载组件
       \'lazy\' => true,
       // db 组件配置
       \'db\' => array(
            // 定义组件名称mysql
           \'mysql\' => array(
                // 通用配置格式
               \'config\' => array(
                   \'read\' => array(
                       \'default\' => array(
                           \'dsn\' => \'mysql:host=localhost;dbname=test;port=3306\',
                            // 用户名
                           \'user\' => \'root\',
                            // 密码
                           \'pass\' => \'root\',
                           // 表前缀 建议为空
                           \'prefix\' => \'\',

                           // 连接选项 数据库需要支持PDO扩展 PDO开启后请取消注释下面行，否则看不到SQL报错或者编码错误
                           \'option\' => array(
                                // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                // PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                                // PDO::MYSQL_ATTR_INIT_COMMAND => \'SET NAMES \\\'UTF8\\\'\',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);',

            );

        foreach($fileLists as $file => $content) :
            if (!$this->check($file)) :
                file_put_contents($file, $content);
            endif;
        endforeach;


    }

    /**
     * check file exists.
     *
     * @param string $file file.
     *
     * @return boolean
     */
    public function check($file)
    {
        return is_file($file) || is_dir($file);

    }

    // parse ason config
    public function parseGlobalAson()
    {
        $asonFile = AR_ROOT_PATH . 'ar.ason';
        if (!file_exists($asonFile)) :
            $oriName = basename(dirname($_SERVER['SCRIPT_FILENAME']));
            $asonInitContent = '{
    "AR_DEBUG": true,
    "AR_AUTO_START_SESSION": true,
    "AR_AS_WEB": true,
    "AR_OUTER_START": false,
    "AR_AS_OUTER_FRAME": false,
    "AR_RUN_AS_SERVICE_HTTP": false,
    "AR_AS_CMD": false,
    "AR_AS_WEB_CLI": false,
    "AR_MAN_NAME": "man",
    "AR_PUBLIC_CONFIG_FILE": "",
    "AR_DEFAULT_APP_NAME": "main",
    "AR_DEFAULT_CONTROLLER": "Index",
    "AR_DEFAULT_ACTION": "index",
    "AR_ORI_NAME": "' . $oriName . '",
    "AR_ORI_PATH": "%AR_ROOT_PATH%%AR_ORI_NAME%%DS%",
    "AR_ASSETS_SERVER_PATH": "%AR_SERVER_PATH%assets/",
    "AR_DATA_PATH": "%AR_ROOT_PATH%data%DS%",
    "CONFIG": {
        "moduleLists": ["main"],
        "theme": "def",
        "REBUILD_TPL_CACHE": true,
        "DEBUG_SHOW_TRACE": true,
        "TPL_SUFFIX": "html"
    }
}
';
            file_put_contents($asonFile, $asonInitContent);
        endif;

        $asonObjString = file_get_contents($asonFile);
        $asonObj = json_decode($asonObjString, true);

        if (!is_array($asonObj)) :
            throw new \ar\core\Exception("global ason file format error ", 30010);
        endif;

        foreach ($asonObj as $asonKey => $ason) :
            if ($asonKey == 'CONFIG') :
                foreach ($ason as $configKey => $config) :
                    \ar\core\Ar::setConfig($configKey, $config);
                endforeach;
            else :

                if (preg_match_all('#%(\S+)%#U', $ason, $match)) :
                    if (!empty($match[1])) :
                        foreach ($match[1] as $matchKey) :
                            $ason = str_replace('%'. $matchKey . '%', constant($matchKey), $ason);
                        endforeach;
                    endif;
                endif;
                defined($asonKey) or define($asonKey, $ason);
            endif;
        endforeach;

    }

    /**
     * generate.
     *
     * @param string $appName appName.
     *
     * @return mixed
     */
    public function generate($appName = '')
    {
        if (!is_dir(AR_DATA_PATH)) :
            mkdir(AR_DATA_PATH);
        endif;

        if (empty($appName)) :
            $appGlobalConfig = \ar\core\cfg();
            if (empty($appGlobalConfig['moduleLists'])) :
                throw new \ar\core\Exception("config can not find param 'moduleLists'!");
            endif;
            $moduleLists = $appGlobalConfig['moduleLists'];
            foreach ($moduleLists as $moduleName) :
                $this->generate($moduleName);
            endforeach;
        endif;

        $this->appName = $appName ? $appName : AR_DEFAULT_APP_NAME;

        $this->basePath = AR_ORI_PATH;

        if (!$this->check(AR_PUBLIC_CONFIG_PATH)) :
            $this->generateFolders();
            $this->generateFiles();
        endif;

    }

    /**
     * generate cmd file.
     *
     * @return void
     */
    public function generateCmdFile()
    {
        $folderMan = AR_CMD_PATH;
        $folderConf = $folderMan . 'Conf' . DS;
        $folderBin = $folderMan . 'Bin' . DS;
        $configFile = $folderConf . 'app.config.ini';
        if (!$this->check($folderMan)) :
            mkdir($folderMan);
        endif;
        if (!$this->check($folderConf)) :
            mkdir($folderConf);
        endif;
        if (!$this->check($folderBin)) :
            mkdir($folderBin);
        endif;
        if (!$this->check($configFile)) :
            file_put_contents($configFile, ';cmd config file
listen_port=10008
listen_ip=127.0.0.1');
        endif;

    }

    /**
     * ar outer start
     *
     * @return void
     */
    public function generateIntoOther()
    {
        $folderMan = AR_MAN_PATH;
        $folderConf = $folderMan . 'Conf' . DS;
        $configFile = $folderConf . 'public.config.php';
        if (!$this->check($folderMan)) :
            mkdir($folderMan);
        endif;
        if (!$this->check($folderConf)) :
            mkdir($folderConf);
        endif;

        if (!$this->check($configFile)) :
            file_put_contents($configFile, '<?php
/**
 * Ar default public config file.
 *
 * @author ycassnr <ycassnr@gmail.com>
 */
return array(
    );');

        endif;

    }

}
