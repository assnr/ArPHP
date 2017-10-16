<?php
namespace ar\comp\Rpc;
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
 * ArJson
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
class Source extends Api
{
    /**
     * parse return data.
     *
     * @param string $parseStr not parsed string.
     *
     * @return string
     */
    protected function parse($parseStr)
    {
        return $parseStr;

    }

    /**
     * getApi.
     *
     * @param string $api    api.
     * @param mixed  $params param.
     *
     * @return string
     */
    protected function getApi($api, $params)
    {
        if (strpos($api, 'http://') === false) :
            if (\ar\core\cfg('URL_MODE') == 'PATH') :
                $prefix = rtrim(empty($this->config['remotePrefix']) ? \ar\core\comp('url.route')->ServerName() : $this->config['remotePrefix'], '/');
            endif;
        else :
            $prefix = '';
        endif;
        if (!empty($params['curlOptions'])) :
            $this->curlOptions = $params['curlOptions'];
            unset($params['curlOptions']);
        endif;

        switch ($this->method) {
        case 'get' :
            if (empty($this->config['remotePrefix'])) :
                $prefix .= \ar\core\url($api, $params);
            else :
                $prefix .= '/' . ltrim($api, '/');
                if (!empty($params)) :
                    $prefix .= '?' . http_build_query($params);
                endif;
            endif;
            break;
        case 'post' :
            $prefix .= empty($this->config['remotePrefix']) ? \ar\core\url($api) : ('/' . ltrim($api, '/'));
            break;
        }

        $url = trim($prefix, '/');

        return $this->remoteCall($url, $params, $this->method);

    }

    /**
     * call api.
     *
     * @param string $api    api.
     * @param mixed  $params parames.
     * @param string $method http method.
     *
     * @return mixed
     */
    public function callApi($api, $params = array(), $method = '')
    {
        if ($method) :
            $this->method = $method;
        else :
            $this->method = empty($this->config['method']) ? 'get' : $this->config['method'];
        endif;

        $result = $this->getApi($api, $params);

        return $this->parse($result);

    }

}
