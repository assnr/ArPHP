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
 * class \ar\core\ApplicationServiceHttp
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
class ApplicationCmd extends Application
{
    // start
    public function start()
    {
        ArWorker::startAll();

    }

}

// worker
class ArWorker
{
     /**
     * 版本号
     * @var string
     */
    const VERSION = '2.0.1';

    /**
     * 状态 启动中
     * @var int
     */
    const STATUS_STARTING = 1;

    /**
     * 状态 运行中
     * @var int
     */
    const STATUS_RUNNING = 2;

    /**
     * 状态 停止
     * @var int
     */
    const STATUS_SHUTDOWN = 4;

    /**
     * 状态 平滑重启中
     * @var int
     */
    const STATUS_RELOADING = 8;

     /**
     * 当前worker状态
     * @var int
     */
    protected static $_status = self::STATUS_STARTING;

    // workers
    public static $workers = array();
    // pids
    public static $pids = array();
    // event
    public static $event = array();
    // connection
    public static $connections = array();
    // worker count
    public $workerCount = 1;
    // protocol
    public $protocol = null;
    // protected
    protected static $masterPidFile;
    // master pid
    protected static $masterPid;
    // daemon start
    protected static $daemonize = false;
    /**
     * 全局统计数据，用于在运行 status 命令时展示
     * 统计的内容包括 workerman启动的时间戳及每组worker进程的退出次数及退出状态码
     * @var array
     */
    private static $_globalStatistics = array(
        'start_timestamp' => 0,
        'worker_exit_info' => array()
    );
    // 设置status文件位置
    private static $_statisticsFile;
    // server socket
    public $serverSocket;
    // 初始化回调函数 msg
    public $onMessage = null;
    // start
    public $onStart = null;
    // stop
    public $onStop = null;
    // close
    public $onClose = null;
    // error
    public $onError = null;
    // connect
    public $onConnect = null;

    public $config = null;

    // instance
    public function __construct($socketName = 'ar_worker', $contextOption = array())
    {
        // 保存worker实例
        $this->workerId = spl_object_hash($this);
        self::$workers[$this->workerId] = $this;
        self::$pids[$this->workerId] = array();

        // 获得实例化文件路径，用于自动加载设置根目录
        $backrace = debug_backtrace();
        $this->appInitPath = dirname($backrace[0]['file']);
        // 用户配置和显示
        $this->socketName = strtolower(trim($socketName));
        $this->context = stream_context_create($contextOption);
        $this->config = \ar\core\cfg($socketName);

    }

    // start workers
    public static function startAll()
    {
         // 关闭opcache
        ini_set('opcache.enable', false);

        // 启动时间戳
        self::$_globalStatistics['start_timestamp'] = time();
        // status file
        self::$_statisticsFile = sys_get_temp_dir() . '/ArPHPServer.status';
        // check
        self::checkEnv();
        // pid file
        self::$masterPidFile = AR_CMD_PATH . 'master.pid';
        // 解析命令
        self::parseCommand();
        // 加载需要的类库
        self::loadFile();
        echo 'started' . "\n";

        if (self::$daemonize) :
            self::deamon();
        endif;

        // save pid
        self::saveMasterPid();
        self::setProcessTitle('AR MASTER PROCESS ');

        self::init();

    }

    // 加载类库
    public static function loadFile()
    {
        foreach(glob(AR_CMD_PATH . 'Model/*.php') as $workerStartFile) :
            require_once $workerStartFile;
        endforeach;
        foreach(glob(AR_CMD_PATH . 'Module/*.php') as $workerStartFile) :
            require_once $workerStartFile;
        endforeach;
        foreach(glob(AR_CMD_PATH . 'Protocol/*.php') as $workerStartFile) :
            require_once $workerStartFile;
        endforeach;
        foreach(glob(AR_CMD_PATH . 'Worker/*.php') as $workerStartFile) :
            require_once $workerStartFile;
        endforeach;
        foreach(glob(AR_CMD_PATH . 'Lib/*.php') as $workerStartFile) :
            require_once $workerStartFile;
        endforeach;

        $workerFiles = glob(AR_CMD_PATH . 'Bin/*_worker.php');
        if (!empty($workerFiles)) :
            foreach($workerFiles as $workerStartFile) :
                require_once $workerStartFile;
            endforeach;
        else :
            exit('has none worker has been seted' . "\n");
        endif;

    }

    // checkenv
    public function checkEnv()
    {
        // 检查扩展
        if (!extension_loaded('pcntl')) :
            exit("Please install pcntl extension.\n");
        endif;

        if (!extension_loaded('posix')) :
            exit("Please install posix extension.\n");
        endif;

        if (!extension_loaded('libevent')) :
            exit("Please install libevent extension.\n");
        endif;

    }

    // 初始化
    public static function init()
    {
        self::$_status = self::STATUS_STARTING;
        self::installSignal();

        // 复制进程
        self::forkWorkers();
        self::monitorWorkers();

    }

    /**
     * 解析运行命令
     * php yourfile.php start | stop | restart | reload | status
     * @return void
     */
    public static function parseCommand()
    {
        // 检查运行命令的参数
        global $argv;
        $start_file = $argv[0];
        if (!isset($argv[1])) :
            exit("Usage: php yourfile.php {start|stop|restart|status}\n");
        endif;
        // 命令
        $command = trim($argv[1]);
        // 子命令，目前只支持-d
        $command2 = isset($argv[2]) ? $argv[2] : '';

        // 检查主进程是否在运行
        $masterPid = @file_get_contents(self::$masterPidFile);
        $masterIsAlive = $masterPid && @posix_kill($masterPid, 0);
        if ($masterIsAlive) :
            if ($command === 'start') :
                echo 'arphp tcp server has running yet ' . "\n";
                exit(0);
            elseif ($command === 'restart') :
                echo 'stopping...' . "\n";
                $masterPid && posix_kill($masterPid, SIGINT);
                echo 'stopped' . "\n";
                sleep(1);
            endif;
        elseif ($command === 'stop') :
            exit('server not running' . "\n");
        endif;

        // 根据命令做相应处理
        switch ($command) {
            case 'start':
            case 'restart':
                echo 'ArPHP Tcp Server starting...' . "\n";
                if ($command2 == '-d') :
                    self::$daemonize = true;
                endif;
                break;
            // 显示 运行状态
            case 'status':
                // 尝试删除统计文件，避免脏数据
                if (is_file(self::$_statisticsFile)) :
                    @unlink(self::$_statisticsFile);
                endif;
                // 向主进程发送 SIGUSR2 信号 ，然后主进程会向所有子进程发送 SIGUSR2 信号
                // 所有进程收到 SIGUSR2 信号后会向 $_statisticsFile 写入自己的状态
                posix_kill($masterPid, SIGUSR2);
                // 睡眠100毫秒，等待子进程将自己的状态写入$_statisticsFile指定的文件
                usleep(100000);
                // 展示状态
                readfile(self::$_statisticsFile);
                exit(0);
            case 'stop':
                echo 'stopping...' . "\n";
                // 主进程发送SIGINT信号，主进程会向所有子进程发送SIGINT信号
                $masterPid && posix_kill($masterPid, SIGINT);
                echo 'stopped' . "\n";
                // @unlink(self::$masterPidFile);
                exit(0);
            // 未知命令
            default :
                 exit("Usage: php yourfile.php {start|stop|restart|status}\n");
        }

    }

    // 成为守护进程
    public static function deamon()
    {
        umask(0);
        $pid = pcntl_fork();
        if ($pid == -1) :
            throw new \ar\core\ExceptionCmd("fork faild", 1200);
        endif;
        if ($pid > 0) :
            exit(0);
        else :
            if (posix_setsid() == -1) :
                throw new \ar\core\ExceptionCmd("set leader sid faild", 1201);
            endif;
            // 脱离终端控制
            $pid = pcntl_fork();
            if ($pid == -1) :
                throw new \ar\core\ExceptionCmd("fork faild", 1200);
            endif;
            if ($pid > 0) :
                exit(0);
            endif;
            global $STDOUT, $STDERR;
            $stdoutFile = '/dev/null';
            $handle = fopen($stdoutFile, "a");
            if ($handle) :
                unset($handle);
                @fclose(STDOUT);
                @fclose(STDERR);
                $STDOUT = fopen($stdoutFile, "a");
                $STDERR = fopen($stdoutFile, "a");
            else :
                throw new \ar\core\ExceptionCmd('can not open stdoutFile ' . $stdoutFile);
            endif;
        endif;

    }

    /**
     * 保存pid到文件中，方便运行命令时查找主进程pid
     * @throws Exception
     */
    protected static function saveMasterPid()
    {
        self::$masterPid = posix_getpid();
        if (false === @file_put_contents(self::$masterPidFile, self::$masterPid)) :
            throw new Exception('can not save pid to ' . self::$masterPidFile);
        endif;

    }

    /**
     * 将当前进程的统计信息写入到统计文件
     * @return void
     */
    protected static function writeStatisticsToStatusFile()
    {
        // 主进程部分
        if (self::$masterPid === posix_getpid()) :
            $loadavg = sys_getloadavg();
            file_put_contents(self::$_statisticsFile, "--------------------------------ArPHPServer GLOBAL STATUS-----------------------------------------\n");
            file_put_contents(self::$_statisticsFile, "PHP version:" . PHP_VERSION."\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile, 'start time:'. date('Y-m-d H:i:s', self::$_globalStatistics['start_timestamp']).'   run ' . floor((time()-self::$_globalStatistics['start_timestamp'])/(24*60*60)). ' days ' . floor(((time()-self::$_globalStatistics['start_timestamp'])%(24*60*60))/(60*60)) . " hours   \n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile, 'load average: ' . implode(", ", $loadavg) . "\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile,  count(self::$pids) . ' workers       ' . count(self::getAllWorkerPids())." processes\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile,  "---------------------------------------PROCESS STATUS-------------------------------------------\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile, "pid\tmemory " . str_pad('worker_name', 9)." connections ".str_pad('total_request', 12) . " " . str_pad('send_fail', 9)." ".str_pad('throw_exception', 15)."\n", FILE_APPEND);
            chmod(self::$_statisticsFile, 0722);
            foreach (self::getAllWorkerPids() as $workerPid) :
                posix_kill($workerPid, SIGUSR2);
            endforeach;
            return;
        endif;

        // 子进程部分
        $worker = current(self::$workers);
        $wrker_status_str = posix_getpid()."\t".str_pad(round(memory_get_usage()/(1024*1024),2)."M", 7)." " .str_pad($worker->socketName, 9);
        $wrker_status_str .= str_pad(ArTcpConnection::$statistics['connection_count'], 12)." ".str_pad(ArTcpConnection::$statistics['total_request'], 12)." ".str_pad(ArTcpConnection::$statistics['send_fail'], 9) . " " . str_pad(ArTcpConnection::$statistics['throw_exception'], 15) . "\n";
        file_put_contents(self::$_statisticsFile, $wrker_status_str, FILE_APPEND);

    }

    /**
     * 安装信号处理函数
     * @return void
     */
    protected static function installSignal()
    {
        // stop
        pcntl_signal(SIGINT,  array('ArWorker', 'signalHandler'), false);
        // reload
        pcntl_signal(SIGUSR1, array('ArWorker', 'signalHandler'), false);
        // status
        pcntl_signal(SIGUSR2, array('ArWorker', 'signalHandler'), false);
        // ignore
        pcntl_signal(SIGPIPE, SIG_IGN, false);

    }

    /**
     * 为子进程重新安装信号处理函数，使用全局事件轮询监听信号
     * @return void
     */
    protected static function reinstallSignal()
    {
        // uninstall stop signal handler
        pcntl_signal(SIGINT,  SIG_IGN, false);
        // uninstall reload signal handler
        pcntl_signal(SIGUSR1, SIG_IGN, false);
        // uninstall  status signal handler
        pcntl_signal(SIGUSR2, SIG_IGN, false);
        // reinstall stop signal handler
        self::$event->add(SIGINT, 'SIGNAL', array('ArWorker', 'signalHandler'));
        //  uninstall  reload signal handler
        self::$event->add(SIGUSR1, 'SIGNAL', array('ArWorker', 'signalHandler'));
        // uninstall  status signal handler
        self::$event->add(SIGUSR2, 'SIGNAL', array('ArWorker', 'signalHandler'));

    }

    /**
     * 信号处理函数
     * @param int $signal
     */
    public static function signalHandler($signal)
    {
        switch($signal)
        {
            // stop
            case SIGINT:
                self::stopAll();
                break;
            // reload
            case SIGUSR1:
                break;
            // show status
            case SIGUSR2:
                self::writeStatisticsToStatusFile();
                break;
        }

    }

    /**
     * 执行关闭流程
     * @return void
     */
    public static function stopAll()
    {
        self::$_status = self::STATUS_SHUTDOWN;
        // 主进程部分
        if (self::$masterPid === posix_getpid()) :
            $worker_pid_array = self::getAllWorkerPids();
            // 向所有子进程发送SIGINT信号，表明关闭服务
            foreach ($worker_pid_array as $worker_pid) :
                posix_kill($worker_pid, SIGINT);
                ArTimer::add(1, 'posix_kill', array($worker_pid, SIGKILL), false);
            endforeach;
        // 子进程部分
        else :
            // 执行stop逻辑
            foreach (self::$workers as $worker) :
                $worker->stop();
            endforeach;
        endif;

    }

    /**
     * 停止当前worker实例
     * @return void
     */
    public function stop()
    {
        // 如果有设置进程终止回调，则执行
        if ($this->onStop) :
            try {
                call_user_func($this->onStop, $this);
            } catch (Exception $e) {
                echo $e->getMessage();
                \ar\core\comp('list.log')->record($e->getMessage(), 'errorstop');
            }
        endif;
        // 删除相关监听事件，关闭_mainSocket
        self::$event->del($this->serverSocket, 'READ');
        @fclose($this->serverSocket);
        exit(0);

    }

    /**
     * 获得所有子进程的pid
     * @return array
     */
    protected static function getAllWorkerPids()
    {
        $pidArray = array();
        foreach(self::$pids as $workerPidArray) :
            foreach ($workerPidArray as $workerPid) :
                $pidArray[$workerPid] = $workerPid;
            endforeach;
        endforeach;
        return $pidArray;

    }

    // libevent
    public function run()
    {
        self::$event = new ArEvent();
        self::reinstallSignal();
        // buiness worker没有
        if ($this->serverSocket) :
            self::$event->add($this->serverSocket, 'READ', array($this, 'acceptConnection'));
        endif;

        // 用全局事件轮询初始化定时器
        ArTimer::init(self::$event);
        // 如果有设置进程启动回调，则执行
        if ($this->onStart) :
            try {
                call_user_func($this->onStart, $this);
            } catch (Exception $e) {
                exit($e->getMessage());
            }
        endif;
        self::$event->loop();
        exit(500);

    }

     /**
     * 设置当前进程的名称，在ps aux命令中有用
     * 注意 需要php>=5.5或者安装了protitle扩展
     * @param string $title
     * @return void
     */
    protected static function setProcessTitle($title)
    {
        // >=php 5.5
        if (function_exists('cli_set_process_title')) :
            @cli_set_process_title($title);
        elseif (extension_loaded('proctitle') && function_exists('setproctitle')) :
            @setproctitle($title);
        endif;

    }

    // listen
    public function listen($ip = '', $port = '')
    {
        if (empty($ip)) :
            $ip = \ar\core\cfg($this->socketName . '.listen_ip', '0.0.0.0');
        endif;
        if (empty($port)) :
            $port = \ar\core\cfg($this->socketName . '.listen_port');
        endif;
        if ($ip && $port) :
            $this->serverSocket = stream_socket_server('tcp://'. $ip . ':' . $port, $errno, $errstr);
            if (!$this->serverSocket) die("$errstr ($errno)");
            stream_set_blocking($this->serverSocket, 0);
            if (self::$event) :
                self::$event->add($this->serverSocket, 'READ', array($this, 'acceptConnection'));
            endif;
        endif;

    }

    /**
     * 创建子进程
     * @return void
     */
    protected static function forkWorkers()
    {
        foreach (self::$workers as $worker) :
            // 初始化监听端口
            if (self::$_status === self::STATUS_STARTING) :
                $worker->listen();
            endif;

            // 创建子进程
            while(count(self::$pids[$worker->workerId]) < $worker->workerCount) :
                self::forkOneWorker($worker);
            endwhile;

        endforeach;

    }

    // 开一个子进程
    public static function forkOneWorker($worker)
    {
        $pid = pcntl_fork();
        if ($pid < 0) :
            die('can not fork' . "\n");
        elseif ($pid === 0) :
            self::$pids = array();
            self::$workers = array($worker->workerId => $worker);
            self::setProcessTitle('AR WORKERP ROCESS : ' . $worker->socketName);
            $worker->run();
        else :
            self::$pids[$worker->workerId][$pid] = $pid;
        endif;

    }

    /**
     * 监控所有子进程的退出事件及退出码
     * @return void
     */
    protected static function monitorWorkers()
    {
        self::$_status = self::STATUS_RUNNING;
        while (true) :
            // 如果有信号到来，尝试触发信号处理函数
            pcntl_signal_dispatch();
            // 挂起进程，直到有子进程退出或者被信号打断
            $status = 0;
            $pid = pcntl_wait($status, WUNTRACED);
            // 有子进程退出
            if ($pid > 0) :
                // 查找是哪个进程组的，然后再启动新的进程补上
                foreach (self::$pids as $workerId => $workerPidArray) :
                    if (isset($workerPidArray[$pid])) :
                        $worker = self::$workers[$workerId];
                        // 清除子进程信息
                        unset(self::$pids[$workerId][$pid]);
                        // 如果不是关闭状态，则补充新的进程
                        if (self::$_status !== self::STATUS_SHUTDOWN) :
                            \ar\core\comp('list.log')->record("worker[".$worker->socketName.":$pid] exit with status $status", 'worker_exit');
                            self::forkWorkers();
                        endif;
                        break;
                    endif;
                endforeach;

            endif;
            if (self::$_status === self::STATUS_SHUTDOWN && !self::getAllWorkerPids()) :
                self::exitAndClearAll();
            endif;
        endwhile;

    }

    /**
     * 退出当前进程
     * @return void
     */
    protected static function exitAndClearAll()
    {
        @unlink(self::$masterPidFile);
        exit(0);

    }

    // ac
    public function acceptConnection($socketServer)
    {
        var_dump('con');
        // static $id = 0;
        $clientSocket = @stream_socket_accept($socketServer);
        // 惊群现象，忽略
        if (false === $clientSocket) :
            return;
        endif;
        $tcpConnection = new ArTcpConnection($clientSocket);
        $tcpConnection->worker = $this;

        // 如果有设置连接回调，则执行

        self::$connections[(int)$clientSocket] = $tcpConnection;
        stream_set_blocking($clientSocket, 0);
        // 链接时回调
        if ($this->onConnect) :
            try {
                echo 'onConnect';
                call_user_func($this->onConnect, $tcpConnection);
            } catch(Exception $e) {
                echo $e->getMessage();
                \ar\core\comp('list.log')->record($e->getMessage(), 'exceptionconnect');
                ArTcpConnection::$statistics['throw_exception']++;
            }
        endif;

        echo 'connect';

    }

    public function timer()
    {
        echo 'timer start';

    }

}

// tcp 链接
class ArTcpConnection
{
    /**
     * 连接状态 连接中
     * @var int
     */
    const STATUS_CONNECTING = 1;

    /**
     * 连接状态 已经建立连接
     * @var int
     */
    const STATUS_ESTABLISH = 2;

    /**
     * 连接状态 连接关闭中，标识调用了close方法，但是发送缓冲区中任然有数据
     * 等待发送缓冲区的数据发送完毕（写入到socket写缓冲区）后执行关闭
     * @var int
     */
    const STATUS_CLOSING = 4;

    /**
     * 连接状态 已经关闭
     * @var int
     */
    const STATUS_CLOSED = 8;

    // 连接状态
    protected $_status = self:: STATUS_CONNECTING;

    // buffer
    protected $readBuffer = '';
    // write buffer
    protected $writeBuffer = '';
    // client socket
    protected $clientSocket = null;

        /**
     * 对端ip
     * @var string
     */
    protected $remoteIp = '';

    /**
     * 对端端口
     * @var int
     */
    protected $remotePort = 0;

    /**
     * 对端的地址 ip+port
     * 值类似于 192.168.1.100:3698
     * @var string
     */
    public $remoteAddress = '';

    // 缓冲区大小
    public static $maxSendBufferSize = 1048576;

    /**
     * status命令的统计数据
     * @var array
     */
    public static $statistics = array(
        'connection_count' => 0,
        'total_request' => 0,
        'throw_exception' => 0,
        'send_fail' => 0,
    );

    // 初始化连接
    public function __construct($clientSocket)
    {
        self::$statistics['connection_count']++;
        $this->_status = self::STATUS_ESTABLISH;
        $this->clientSocket = $clientSocket;
        ArWorker::$event->add($clientSocket, 'READ', array($this, 'read'), null, 10);

    }

    /**
     * get remote ip
     * @return string
     */
    public function getRemoteIp()
    {
        if(!$this->remoteIp)
        {
            $this->remoteAddress = stream_socket_get_name($this->clientSocket, true);
            if($this->remoteAddress)
            {
                list($this->remoteIp, $this->remotePort) = explode(':', $this->remoteAddress, 2);
                $this->remotePort = (int)$this->remotePort;
            }
        }
        return $this->remoteIp;
    }

    /**
     * get remote port
     */
    public function getRemotePort()
    {
        if(!$this->remotePort)
        {
            $this->remoteAddress = stream_socket_get_name($this->clientSocket, true);
            if($this->remoteAddress)
            {
                list($this->remoteIp, $this->remotePort) = explode(':', $this->remoteAddress, 2);
                $this->remotePort = (int)$this->remotePort;
            }
        }
        return $this->remotePort;
    }

    /**
     * consume recvBuffer
     * @param int $length
     */
    public function consumeRecvBuffer($length)
    {
        $this->readBuffer = substr($this->readBuffer, $length);

    }

    // read
    public function read($client)
    {
        $fdKey = (int)$client;
        while(($buffer = fread($client, 8192)) || is_numeric($buffer)) :
            $this->readBuffer .= $buffer;
        endwhile;
        if ($this->readBuffer !== '') :
            if (!$this->worker->onMessage) :
                $this->readBuffer = '';
                return;
            endif;
            while ($this->readBuffer !== '') {
                if (feof($client)) :
                    // 清空已接收的buffer
                    $this->close();
                    return;
                endif;
                if ($protocol = $this->worker->protocol) :
                    $length = call_user_func($protocol . '::input', $this->readBuffer, $this);
                    if ($length === 0) :
                        break;
                    else :
                        if ($length > 0 && $length <= self::$maxSendBufferSize) :
                            // need more buffer
                            if ($length > strlen($this->readBuffer)) :
                               break;
                            endif;
                        else :
                            // CLOSE ERROR SEND PACK CLIENDS
                            \ar\core\comp('list.log')->record(time(), 'error_package_length');
                            $this->close();
                            return;
                        endif;

                        self::$statistics['total_request']++;
                        $message = substr($this->readBuffer, 0, $length);
                        $message = call_user_func($protocol . '::decode', $message, $this);
                        $this->readBuffer = substr($this->readBuffer, $length);
                        // 解析失败
                        if ($message === false) :
                            $this->close();
                        else :
                            if ($this->worker->onMessage) :
                               // 处理数据包
                               try {
                                   call_user_func($this->worker->onMessage, $message, $this);
                               } catch(Exception $e) {
                                   echo $e->getMessage();
                                   // 日志
                                   \ar\core\comp('list.log')->record($e->getMessage(), 'readprotocol');
                                   self::$statistics['throw_exception']++;
                               }
                            endif;
                        endif;
                    endif;
                else :
                    self::$statistics['total_request']++;
                    if ($this->worker->onMessage) :
                       // 处理数据包
                       try {
                           call_user_func($this->worker->onMessage, $this->readBuffer, $this);
                       } catch(Exception $e) {
                           echo $e->getMessage();
                           \ar\core\comp('list.log')->record($e->getMessage(), 'readnoprotocol');
                           self::$statistics['throw_exception']++;
                       }
                    endif;
                    $this->readBuffer = '';
                endif;

            }
        else :
            if (feof($client)) :
                $this->close();
            endif;
        endif;

    }

    // write msg
    public function write()
    {
        // \ar\core\comp('list.log')->record(strlen($this->writeBuffer), 'buffer' . (int)$this->clientSocket);
        if ($this->writeBuffer !== '') :
            $length = @fwrite($this->clientSocket, $this->writeBuffer);
            $bufferLength = strlen($this->writeBuffer);
            if ($length === $bufferLength) :
                ArWorker::$event->del($this->clientSocket, 'WRITE');
                $this->writeBuffer = '';
                if ($this->_status == self::STATUS_CLOSING) :
                    $this->destruct();
                endif;
                return true;
            elseif ($length > 0) :
                $this->writeBuffer = substr($this->writeBuffer, $length);
            else :
                if (feof($this->clientSocket)) :
                    self::$statistics['send_fail']++;
                    // close 不掉的时候 直接清除 对象
                    $this->destruct();
                endif;
            endif;
        endif;

    }

    // close client 增加强制退出回收资源
    public function close($data = null, $closeForce = false)
    {
        if ($data !== null) :
            $this->send($data);
        endif;
        $this->_status = self::STATUS_CLOSING;
        if ($this->writeBuffer === '' || $closeForce) :
            // 最后一步
            $this->destruct();
        endif;

    }

    // send to client
    public function send($message, $raw = false)
    {
        // 如果连接已经关闭，则返回false
        if ($this->_status == self::STATUS_CLOSED) :
            return false;
        endif;

        if ($raw === false && $protocol = $this->worker->protocol) :
            $message = call_user_func($protocol . '::encode', $message, $this);
        endif;

        if ($this->writeBuffer === '') :
            $fdKey = (int)$this->clientSocket;

            $bufferLength = strlen($message);
            $length = @fwrite($this->clientSocket, $message);
            if ($length === $bufferLength) :
                return true;
            // local close
            elseif ($length > 0) :
                // status统计发送失败次数
                // self::$statistics['send_fail']++;
                $this->writeBuffer = substr($message, $length);
            else :
                if (!is_resource($this->clientSocket) || feof($this->clientSocket)) :
                    self::$statistics['send_fail']++;
                    $this->close();
                    return false;
                endif;
            endif;
            ArWorker::$event->add($this->clientSocket, 'WRITE', array($this, 'write'));
            return null;
        else :
             // 检查发送缓冲区是否已满
            if (self::$maxSendBufferSize <= strlen($this->writeBuffer) + strlen($message)) :
                // 为status命令统计发送失败次数
                self::$statistics['send_fail']++;
                \ar\core\comp('list.log')->record('cs:' . (strlen($this->writeBuffer) + strlen($message)) . '>' . self::$maxSendBufferSize . ' msg : ' . $message, 'msg_full' . __FUNCTION__);
                //\ar\core\comp('list.log')->record($this->writeBuffer, 'msg_fulling_writebuffer' . __FUNCTION__);
                // 如果有设置失败回调，则执行
                if ($this->worker->onError) :
                    call_user_func($this->worker->onError, $this);
                endif;
                return false;
            endif;
            // 将数据放入放缓冲区
            $this->writeBuffer .= $message;
        endif;

    }

    /**
     * 析构函数
     * @return void
     */
    public function destruct()
    {
        if ($this->worker->onClose) :
            try {
                call_user_func($this->worker->onClose, $this);
            } catch (Exception $e) {
                \ar\core\comp('list.log')->record($e->getMessage(), 'exceptionclose');
                self::$statistics['throw_exception']++;
            }
        endif;

        if ($this->_status !== self::STATUS_CLOSED) :
            // 统计数据
            self::$statistics['connection_count']--;
            $fdKey = (int)$this->clientSocket;
            fclose($this->clientSocket);
            unset(ArWorker::$connections[$fdKey]);
            ArWorker::$event->del($this->clientSocket, 'READ');
            ArWorker::$event->del($this->clientSocket, 'WRITE');
            $this->_status = self::STATUS_CLOSED;
        endif;

        echo 'closed' . "\n";

    }

}

// event 事件
class ArEvent
{
    // eventBase
    public static $eventBase = null;
    // 所有的事件
    protected static $allEvents = array();
    // 定时事件
    protected static $eventSignal = array();
    // 定时事件
    protected static $eventTimer = array();

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        self::$eventBase = event_base_new();

    }

    // 添加
    public function add($fd, $flag, $callBack, $args = null)
    {
        $fdKey = (int)$fd;
        switch ($flag) {
            case 'SIGNAL':
                $realFlag = EV_SIGNAL | EV_PERSIST;
                $this->eventSignal[$fdKey] = event_new();
                if (!event_set($this->eventSignal[$fdKey], $fd, $realFlag, $callBack, null)) :
                    return false;
                endif;
                if (!event_base_set($this->eventSignal[$fdKey], self::$eventBase)) :
                    return false;
                endif;
                if (!event_add($this->eventSignal[$fdKey])) :
                    return false;
                endif;
                return true;
                break;
            case 'TIMER':
            case 'TIMER_ONCE':
                $event = event_new();
                $timerId = (int)$event;
                if (!event_set($event, 0, EV_TIMEOUT, array($this, 'timerCallback'), $timerId)) :
                    echo 'af1';
                    return false;
                endif;
                if (!event_base_set($event, self::$eventBase)) :
                    echo 'af2';
                    return false;
                endif;
                // 间隔重复执行
                $timeInterval = $fd * 1000000;
                if (!event_add($event, $timeInterval)) :
                    echo 'af3';
                    return false;
                endif;
                self::$eventTimer[$timerId] = array($callBack, (array)$args, $event, $flag, $timeInterval);
                return $timerId;
                break;
            case 'READ':
            case 'WRITE':
                $fdKey = (int)$fd;
                $realFlag = $flag == 'READ' ? EV_READ | EV_PERSIST : EV_WRITE | EV_PERSIST;
                $event = event_new();
                if (!event_set($event, $fd, $realFlag, $callBack, null)) :
                    return false;
                endif;
                if (!event_base_set($event, self::$eventBase)) :
                    return false;
                endif;
                if (!event_add($event)) :
                    return false;
                endif;
                $this->allEvents[$fdKey][$flag] = $event;
                return true;
                break;
        }

    }

    /**
     * 删除事件
     */
    public function del($fd ,$flag)
    {
        switch($flag)
        {
            case 'READ':
            case 'WRITE':
                $fdKey = (int)$fd;
                if (isset($this->allEvents[$fdKey][$flag])) :
                    event_del($this->allEvents[$fdKey][$flag]);
                    unset($this->allEvents[$fdKey][$flag]);
                endif;
                if (empty($this->allEvents[$fdKey])) :
                    unset($this->allEvents[$fdKey]);
                endif;
                break;
            case 'SIGNAL':
                $fdKey = (int)$fd;
                if (isset(self::$eventSignal[$fdKey])) :
                    event_del(self::$eventSignal[$fdKey]);
                    unset(self::$eventSignal[$fdKey]);
                endif;
                break;
            case 'TIMER':
            case 'TIMER_ONCE':
                // 这里 fd 为timerid
                if (isset(self::$eventTimer[$fd])) :
                    event_del(self::$eventTimer[$fd][2]);
                    unset(self::$eventTimer[$fd]);
                endif;
                break;
        }
        return true;

    }

    /**
     * 定时器回调.
     * @param null $_null
     * @param null $_null
     * @param int  $timerId
     */
    protected function timerCallback($_null, $_null, $timerId)
    {
        // 如果是连续的定时任务，再把任务加进去
        if (self::$eventTimer[$timerId][3] == 'TIMER') :
            event_add(self::$eventTimer[$timerId][2], self::$eventTimer[$timerId][4]);
        endif;
        try {
            // 执行任务
            call_user_func_array(self::$eventTimer[$timerId][0], self::$eventTimer[$timerId][1]);
        } catch(Exception $e) {
            echo $e->getMessage();

        }
    }

    /**
     * 删除所有定时器
     * @return void
     */
    public function clearAllArTimer()
    {
        foreach (self::$eventTimer as $task_data) :
            event_del($task_data[2]);
        endforeach;
        self::$eventTimer = array();
    }

    /**
     * 事件循环
     * @see EventInterface::loop()
     */
    public function loop()
    {
        event_base_loop(self::$eventBase);

    }

}

class ArTimer
{
    /**
     * event
     * @var event
     */
    protected static $_event = null;
    protected static $_tasks = array();

    /**
     * 初始化
     * @return void
     */
    public static function init($event = null)
    {
        if ($event) :
            self::$_event = $event;
        endif;

    }

    /**
     * 删除定时器
     * @param $timer_id
     */
    public static function del($timer_id)
    {
        if(self::$_event)
        {
            return self::$_event->del($timer_id, 'TIMER');
        }
    }

    /**
     * 添加一个定时器
     * @param int $time_interval
     * @param callback $func
     * @param mix $args
     * @return void
     */
    public static function add($time_interval, $func, $args = array(), $persistent = true)
    {
        if($time_interval <= 0)
        {
            echo new Exception("bad time_interval");
            return false;
        }

        if(self::$_event)
        {
            return self::$_event->add($time_interval, $persistent ? 'TIMER' : 'TIMER_ONCE', $func, $args);
        }

    }

}

// 文本协议
class ArProtocolText
{
    /**
     * 检查包的完整性
     * 如果能够得到包长，则返回包的长度，否则返回0继续等待数据
     * @param string $buffer
     */
    public static function input($buffer, $connection)
    {
        // 获得换行字符"\n"位置
        $pos = strpos($buffer, "\n");
        // 没有换行符，无法得知包长，返回0继续等待数据
        if($pos === false)
        {
            return 0;
        }
        // 有换行符，返回当前包长，包含换行符
        return $pos + 1;

    }

    /**
     * 打包，当向客户端发送数据的时候会自动调用
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer, $connection)
    {
        // 加上换行
        return $buffer . "\n";

    }

    public static function decode($buffer, $connection)
    {
        // 去掉换行
        return trim($buffer);

    }

}

// 上下文
class ArContext
{
    // holder
    static private $_holder = array();

    // get value
    public static function get($key)
    {
        if (array_key_exists($key, self::$_holder)) :
            return self::$_holder[$key];
        else :
            return null;
        endif;

    }

    // set value to holder
    public static function set($key, $value)
    {
        self::$_holder[$key] = $value;

    }

    // flush
    public static function flush()
    {
        self::$_holder = array();

    }

}
