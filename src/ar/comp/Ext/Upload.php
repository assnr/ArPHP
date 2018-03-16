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
 * l
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
class Upload extends Component
{
    // upload destination folder
    public $dest = '';
    // upload error
    public $errorMsg = null;
    // upload field
    protected $upField = '';

    /**
     * get errorMsg.
     *
     * @return $mixed
     */
    public function errorMsg()
    {
        return $this->errorMsg;

    }

    // mimemap
    static public $extensionMap = array(
            'img' => array(
                'jpg', 'jpe', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'svg', 'ico',
            ),
        );

    /**
     * upload.
     *
     * @param string $upField   upload field.
     * @param string $dest      upload destination.
     * @param string $extension allow file extension.
     * @param string $picName   图片名称
     *
     * @return mixed
     */
    public function upload($upField, $dest = '', $extension = 'img', $picName = '')
    {
        $this->errorMsg = null;

        $this->upField = $upField;

        if (!empty($_FILES[$this->upField]) && empty($_FILES['error']) && is_uploaded_file($_FILES[$this->upField]['tmp_name'])) :
            if ($extension == 'all' || $this->checkFileType($this->getFileExtensionName($_FILES[$this->upField]['name']), $extension)) :

                if (empty($dest)) :
                    $dest = \ar\core\cfg('DIR.UPLOAD') . date('Ymd', time());
                endif;

                if (!is_dir($dest)) :
                    mkdir($dest, 0777, true);
                endif;

                $upFileName = $picName ? $picName : $this->generateFileName();

                $destFile = rtrim($dest, DS) . DS . $upFileName;

                if (move_uploaded_file($_FILES[$this->upField]['tmp_name'], $destFile)) :

                else :
                    $this->errorMsg = '上传出错';
                endif;

            endif;

        else :
            if (empty($_FILES)) :
                $this->errorMsg = 'empty $_FILES uploaded file maybe exceeds the upload_max_filesize(' . ini_get('upload_max_filesize') . ') directive in php.ini';
            else :
                if (empty($_FILES[$this->upField])) :
                    $this->errorMsg = "Filed '$upField' invalid";
                else :
                    $this->errorMsg = $this->errorcodeToMessage($_FILES[$this->upField]['error']);
                endif;
            endif;
        endif;

        if (!!$this->errorMsg) :
            return false;
        else :
            return ['filename' => $upFileName, 'full_path_name' => $destFile];
        endif;

    }

    /**
     * upload.
     *
     * @param int $code upload errorcode.
     *
     * @return mixed
     */
    private function errorcodeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize(" . ini_get('upload_max_filesize') . ") directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;

    }

    /**
     * check file type valided.
     *
     * @param string $fileType  fileType.
     * @param string $extension file ext.
     *
     * @return boolean
     */
    protected function checkFileType($extension, $aExtension = 'img')
    {
        if (is_array($aExtension)) :
            if (!in_array($extension, $aExtension)) :
                $this->errorMsg ="仅支持 " . implode(',', $aExtension) . " 类型";
            endif;
        else :
            if (array_key_exists($aExtension, self::$extensionMap)) :
                if (!in_array($extension, self::$extensionMap[$aExtension])) :
                    $this->errorMsg = "仅支持 " . implode(',', self::$extensionMap[$aExtension]). " 类型";
                endif;
            elseif ($extension != $aExtension) :
                $this->errorMsg ="仅支持{$aExtension}类型";
            endif;
        endif;

        return !$this->errorMsg;

    }

    /**
     * generate filename.
     *
     * @return string
     */
    protected function generateFileName()
    {
        return md5(time() . rand()) . '.' . $this->getFileExtensionName($_FILES[$this->upField]['name']);

    }

    /**
     * get file extension
     *
     * @return void
     */
    protected function getFileExtensionName($fileName)
    {
        return strtolower(substr($fileName, strrpos($fileName, '.') + 1));

    }

}
