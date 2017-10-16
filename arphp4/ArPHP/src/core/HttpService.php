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
 * class ArService
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
class HttpService
{
    /**
     * response to client.
     *
     * @param mixed $data response data.
     *
     * @return void
     */
    protected function response($data = '')
    {
        return \ar\core\comp('rpc.service')->response($data);

    }

    /**
     * first exec func.
     *
     * @return mixed
     */
    public function init($auth)
    {
        $this->checkAuth($auth);

    }

    // auth param
    public function checkAuth($param)
    {


    }

}
