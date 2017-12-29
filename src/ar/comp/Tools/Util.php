<?php
namespace ar\comp\Tools;
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
 * ArUtil
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
class Util extends Component
{
    // 获取客户端ip
    public function getClientIp()
    {
        $unknown = 'unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) :
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) :
            $ip = $_SERVER['REMOTE_ADDR'];
        endif;

        if (false !== strpos($ip, ','))
            $ip = reset(explode(',', $ip));

        return $ip;

    }

    // 获取对外显示的公网ip
    public function getServerIp($os = 'linux', $cli = true)
    {
        if ($os == 'linux' && $cli == true) :
            $ss = @exec('/sbin/ifconfig eth0 | sed -n \'s/^ *.*addr:\\([0-9.]\\{7,\\}\\) .*$/\\1/p\'',$arr);
            $server_ip = $arr[0];
        else :
            if (isset($_SERVER)) :
                if ($_SERVER['SERVER_ADDR']) :
                    $server_ip = $_SERVER['SERVER_ADDR'];
                else :
                    $server_ip = $_SERVER['LOCAL_ADDR'];
                endif;
            else :
                $server_ip = getenv('SERVER_ADDR');
            endif;
        endif;

        return $server_ip;

    }

    // 截取字符串
    public function substr_cut($str, $len, $charset="utf-8"){
        // 如果截取长度小于等于0，则返回空
        if (!is_numeric($len) or $len <= 0) :
            return '';
        endif;

        //如果截取长度大于总字符串长度，则直接返回当前字符串
        $sLen = strlen($str);
        if ($len >= $sLen) :
            return $str;
        endif;

        if (strtolower($charset) == "utf-8") :
            $len_step = 3;
        else :
            $len_step = 2;
        endif;

        $len_i = 0;
        $substr_len = 0;
        for($i=0; $i < $sLen; $i++) :
            if ($len_i >= $len)
                break;

            if(ord(substr($str,$i,1)) > 0xa0) :
                $i += $len_step - 1;
                $substr_len += $len_step;
            else :
                $substr_len++;
            endif;
            $len_i++;
        endfor;
        $result_str = substr($str, 0, $substr_len);
        return $result_str;

    }

    // 生成随机值
    public function randpw($len = 8, $format = 'ALL')
    {
        $is_abc = $is_numer = 0;
        $password = $tmp ='';
        switch ($format) {
            case 'ALL':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
            case 'CHAR':
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
            case 'NUMBER':
            $chars='0123456789';
            break;
            default :
            $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
        }

        while (strlen($password) < $len) :
            $tmp =substr($chars, (mt_rand()%strlen($chars)), 1);
            if (($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR') :
                $is_numer = 1;
            endif;
            if (($is_abc <> 1 && preg_match('/[a-zA-Z]/', $tmp)) || $format == 'NUMBER') :
                $is_abc = 1;
            endif;
            $password .= $tmp;
        endwhile;

        if ($is_numer <> 1 || $is_abc <> 1 || empty($password)) :
            $password = $this->randpw($len, $format);
        endif;

        return $password;

    }

    // removes files and non-empty directories
    public function rmdir($dir)
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
            if ($file != "." && $file != "..") $this->rmdir("$dir/$file");
            rmdir($dir);
        }
        else if (file_exists($dir)) unlink($dir);

    }

    // copies files and non-empty directories
    public function copy($src, $dst)
    {
        if (is_dir($src)) {
            if (file_exists($dst)) :
                return false;
            endif;
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file)
                if ($file != "." && $file != "..") $this->copy("$src/$file", "$dst/$file");
        }
        else if (file_exists($src)) copy($src, $dst);

    }

    // 数组判断
    public function dstrpos($bundle, $needles)
    {
        $rt = false;
        if (is_array($needles)) :
            foreach ($needles as $needle) :
                $rt = strpos($bundle, $needle);
                if ($rt !== false) :
                    break;
                endif;
            endforeach;
        endif;
        return $rt;

    }

    // 是否手机
    public function isMobile()
    {
        $mobile = array();
        static $mobilebrowser_list = array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
                    'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
                    'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
                    'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
                    'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
                    'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
                    'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
        $pad_list = array('pad', 'gt-p1000');
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if ($this->dstrpos($useragent, $pad_list) !== false) {
            return false;
        }

        if ($this->dstrpos($useragent, $mobilebrowser_list) !== false) {
            return true;
        }

        $brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');

        if ($this->dstrpos($useragent, $brower) !== false) {
            return false;
        }

        if(\ar\core\get('mobile') === 'yes') {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 是否是AJAx提交的
     * @return bool
     */
   public function isAjax()
   {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            return true;
        } else {
            return false;
        }

    }

    /**
     * 是否是GET提交的
     */
    public function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] == 'GET' ? true : false;

    }

    /**
     * 是否是POST提交的
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;

    }

    /**
     * 是否是POST提交的
     */
    public function isPut()
    {
        return $_SERVER['REQUEST_METHOD'] == 'PUT' ? true : false;

    }

}
