<?php
namespace ar\comp\Hash;
use ar\core\Ar as Ar;
use ar\comp\Component as Component;
/**
 * ArPHP A Strong Performence PHP FrameWork ! You Should Have.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Core.Component.Hash
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  GIT: 1: coding-standard-tutorial.xml,v 1.0 2014-5-01 18:16:25 cweiske Exp $
 * @link     http://www.arphp.org
 */

/**
 * ArMcrypt
 *
 * default hash comment :
 *
 * <code>
 *  #This is a hash comment, which is prohibited.
 *  $hello = 'hello';
 * </code>
 *
 * @category ArPHP
 * @package  Core.Component.Hash
 * @author   yc <ycassnr@gmail.com>
 * @license  http://www.arphp.org/licence MIT Licence
 * @version  Release: @package_version@
 * @link     http://www.arphp.org
 */
class Openssl extends Component
{

    /**
     * 组件配置
    'hash' => array(
            'openssl' => array(
                'config' => array(
                    // 公钥
                    'pub_key' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3//sR2tXw0wrC2DySx8vNGlqt
3Y7ldU9+LBLI6e1KS5lfc5jlTGF7KBTSkCHBM3ouEHWqp1ZJ85iJe59aF5gIB2kl
Bd6h4wrbbHA2XE1sq21ykja/Gqx7/IRia3zQfxGv/qEkyGOx+XALVoOlZqDwh76o
2n1vP1D+tD3amHsK7QIDAQAB
-----END PUBLIC KEY-----',
                    // 私钥
                    'prv_key' => '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC3//sR2tXw0wrC2DySx8vNGlqt3Y7ldU9+LBLI6e1KS5lfc5jl
TGF7KBTSkCHBM3ouEHWqp1ZJ85iJe59aF5gIB2klBd6h4wrbbHA2XE1sq21ykja/
Gqx7/IRia3zQfxGv/qEkyGOx+XALVoOlZqDwh76o2n1vP1D+tD3amHsK7QIDAQAB
AoGBAKH14bMitESqD4PYwODWmy7rrrvyFPEnJJTECLjvKB7IkrVxVDkp1XiJnGKH
2h5syHQ5qslPSGYJ1M/XkDnGINwaLVHVD3BoKKgKg1bZn7ao5pXT+herqxaVwWs6
ga63yVSIC8jcODxiuvxJnUMQRLaqoF6aUb/2VWc2T5MDmxLhAkEA3pwGpvXgLiWL
3h7QLYZLrLrbFRuRN4CYl4UYaAKokkAvZly04Glle8ycgOc2DzL4eiL4l/+x/gaq
deJU/cHLRQJBANOZY0mEoVkwhU4bScSdnfM6usQowYBEwHYYh/OTv1a3SqcCE1f+
qbAclCqeNiHajCcDmgYJ53LfIgyv0wCS54kCQAXaPkaHclRkQlAdqUV5IWYyJ25f
oiq+Y8SgCCs73qixrU1YpJy9yKA/meG9smsl4Oh9IOIGI+zUygh9YdSmEq0CQQC2
4G3IP2G3lNDRdZIm5NZ7PfnmyRabxk/UgVUWdk47IwTZHFkdhxKfC8QepUhBsAHL
QjifGXY4eJKUBm3FpDGJAkAFwUxYssiJjvrHwnHFbg0rFkvvY63OSmnRxiL4X6EY
yI9lblCsyfpl25l7l5zmJrAHn45zAiOoBrWqpM5edu7c
-----END RSA PRIVATE KEY-----',
                ),
            ),
        ),

    */
    // public key
    private $_pub_key;
    // source
    private $_source_pub_key;

    // pri key
    private $_prv_key;
    // source
    private $_source_prv_key;

    /**
     * init.
     *
     * @param mixed  $config config.
     * @param string $class  class holder.
     *
     * @return Object
     */
    static public function init($config = array(), $class = __CLASS__)
    {
        $hashObject = parent::init($config, $class);
        if (empty($hashObject->config['pub_key'])) :
            throw new \ar\core\Exception("openssl public key invalid", 3001);
        endif;
        $hashObject->setKey($hashObject->config['pub_key']);
        if (isset($hashObject->config['prv_key'])) :
            $hashObject->setKey($hashObject->config['prv_key'], false);
        endif;
        return $hashObject;

    }

    // 改变key
    public function setKey($key = '', $public = true)
    {
        if ($key) :
            if ($public) :
                $this->_pub_key = $key;
                $this->_source_pub_key = openssl_pkey_get_public($this->_pub_key);
            else :
                $this->_prv_key = $key;
                $this->_source_prv_key = openssl_pkey_get_private($this->_prv_key);
            endif;
        endif;
        return true;

    }

    /**
     * encrypt.
     *
     * @param string  $plaintext plain.
     * @param boolean $public    public encrypt.
     *
     * @return string
     */
    public function encrypt($plaintext, $public = true)
    {
        $ciphertext = '';
        if ($public) :
            openssl_public_encrypt($plaintext, $ciphertext, $this->_source_pub_key);
        else :
            openssl_private_encrypt($plaintext, $ciphertext, $this->_source_prv_key);
        endif;
        $ciphertext = base64_encode($ciphertext);
        return $ciphertext;

    }

    /**
     * decrypt.
     *
     * @param string  $ciphertext_base64 ciphertext.
     * @param boolean $public            public encrypt.
     *
     * @return string
     */
    public function decrypt($ciphertext_base64, $public = true)
    {
        if ($public) :
            openssl_public_decrypt(base64_decode($ciphertext_base64), $decrypted, $this->_source_pub_key);
        else :
            openssl_private_decrypt(base64_decode($ciphertext_base64), $decrypted, $this->_source_prv_key);
        endif;
        return $decrypted;

    }

}
