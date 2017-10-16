<?php
namespace ar\comp\Ext;
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
 * display std out msg
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
class Out extends Component
{
    /**
     * json display.
     *
     * @param mixed $data    jsondata.
     * @param array $options option.
     *
     * @return mixed
     */
    public function json($data = array(), array $options = array())
    {
        if (empty($options['showJson']) || $options['showJson'] == true) :
            // header('charset:utf-8');
            // header('Content-type:text/javascript');
            if (empty($options['data'])) :
                $retArr = array(
                    'ret_code' => '1000',
                    'ret_msg' => '',
                );

                if (is_array($data)) :
                    if (!isset($data['ret_code']) || !isset($data['ret_msg'])) :
                        $retArr['data'] = $data;
                        $retArr['total_lines'] = Ar::c('validator.validator')->checkMutiArray($data) ? (string)count($data) : 1;
                    else :
                        if (!empty($data['error_msg']) && empty($data['ret_code'])) :
                            $retArr['ret_code'] = "1001";
                        endif;
                        $retArr = array_merge($retArr, $data);
                    endif;
                    $retArr = array_merge($retArr, $options);
                else :
                    $retArr['ret_msg'] = $data;
                endif;
            else :
                $retArr = $data;
            endif;
            echo json_encode($retArr);
            // json_encode chinese transfer bug
            // json_decode 返回不了数组
            // echo urldecode(json_encode(\ar\core\comp('format.format')->urlencode($retArr)));
            // crashed when use exit in contorller this->showJson() php 5.2.6
            // exit;
        else :
            return $data;
        endif;

    }

    /**
     * show debug info.
     *
     * @param string  $msg  out msg.
     * @param string  $tag  debug stage.
     * @param boolean $show if display.
     *
     * @return void
     */
    public function deBug($msg = '', $tag = 'TRACE', $show = false)
    {
        static $deBugMsg = array();

        if (!array_key_exists($tag, $deBugMsg)) :
            $deBugMsg[$tag] = '';
        endif;

        if ($msg) :
            if (preg_match("#\[[A-Z_]+\]$#", $msg)) :
                $msg = "<b>" . $msg . "</b>";
            else :
                $msg = "&nbsp;&nbsp;" . $msg;
            endif;
            $deBugMsg[$tag] .= $msg . "<br>";
        endif;

        if ($show && !empty($deBugMsg[$tag])) :
            $showContentBox = array(
                    'header' => '<div style="width:98%;bottom:30px"><div style="border-top:1px #666 dashed;background:#f1f1f1;text-align:center;font-size:20px;margin:10px 0px 10px;">[DEBUG ' . $tag . ' INFO] </div>',
                    'showMsg' => '<div style="padding:5px;background:#f3f3f1;line-height:30px">' . $deBugMsg[$tag] . '</div>',
                    'footer' => '</div>',
                );

            if (\ar\core\cfg('DEBUG_SHOW_TRACE')) :
                $showContentBox['trance'] = '<div style="background:#f8f8f8">RUN TIME : ' . (microtime(1) - AR_START_TIME) . 's</div>';
            endif;

            if (\ar\core\cfg('DEBUG_LOG')) :
                \ar\core\comp('list.log')->record($showContentBox, 'debug');
                switch ($tag) {
                    case 'EXCEPTION':
                        // $segFile = \ar\core\cfg('DIR.SEG') . 'Redirect' . DS . '';
                        Header("HTTP/1.1 404 Not Found");
                        \ar\core\seg(array('segKey' => 'Redirect/404'));
                        break;
                    case 'SERVER_ERROR':
                        Header("HTTP/1.1 500 App Error");
                        \ar\core\seg(array('segKey' => 'Redirect/500'));
                        break;
                    default:
                        break;
                }
            else :
                if (AR_AS_WEB_CLI) :
                    foreach ($showContentBox as &$showBoxMsg) :
                        $showBoxMsg = strip_tags($showBoxMsg) . PHP_EOL;
                    endforeach;
                endif;
                $outPutMsg = join($showContentBox, '');

                echo $outPutMsg;
            endif;
            $deBugMsg[$tag] = '';
        endif;

    }

    /**
     * array transfer to xml.
     *
     * @param array $array array.
     * @param mixed $xml   xml object.
     *
     * @return string
     */
    public function array2xml(array $array, $xml = false, $root = 'root')
    {
        if ($xml === false) :
            $xml = new SimpleXMLElement('<' . $root . '/>');
        endif;

        foreach ($array as $key => $value) :
            if (is_array($value)) :
                $this->array2xml($value, $xml->addChild($key));
            else :
                $xml->addChild($key, $value);
            endif;
        endforeach;

        return $xml->asXML();
    }

    /**
     * Xml 转 数组, 包括根键.
     *
     * @param string $xml string xml.
     *
     * @return mixed
     */
    public function xml2array($xml, $trimCdata = false)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) :
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) :
                $subxml = $matches[2][$i];
                $key = $matches[1][$i];
                if (preg_match($reg, $subxml)) :
                    $arr[$key] = $this->xml2array($subxml, $trimCdata);
                else :
                    if ($trimCdata) :
                        $arr[$key] = str_replace(array('<![CDATA[', ']]>'), '', $subxml);
                    else :
                        $arr[$key] = $subxml;
                    endif;
                endif;
            endfor;
        endif;
        return $arr;

    }

}
