<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Util;
use System\Environment\Env;


/**
 * Class FileUploader
 * @package System\Util
 */
class FileUploader 
{
    /**
     * @var int
     */
    private $max_filesize;

    /**
     * @var array
     */
    private $extensions = ['*'];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var null|callable
     */
    private $name_handler = null;

    /**
     * @param array $files
     * @param array $extensions
     * @param int $max_size
     */
    public function __construct($files, $extensions = ['*'], $max_size = 32)
    {
        $this->files = $files;
        $this->allowed_extensions($extensions);
        $this->max_filesize($max_size);
    }

    /**
     * @param $callable
     */
    public function set_naming_handler($callable)
    {
        if ( !is_callable($callable) ) {
            user_error('Handler is not callable', E_USER_ERROR);
            exit;
        }

        $this->name_handler = $callable;
    }

    /**
     * @param null $extensions
     * @return array
     */
    public function allowed_extensions($extensions = null)
    {
        if ( is_null($extensions) ) {
            return $this->extensions;
        }

        $this->extensions = array_map(function($e){ return strtolower($e); }, $extensions);
    }

    /**
     * @param null $max_size
     * @return int
     */
    public function max_filesize($max_size = null)
    {
        if ( is_null($max_size) ) {
            return $this->max_filesize;
        }

        $this->max_filesize = $max_size * 1024 * 1024;
    }

    /**
     * @param string $upload_to
     * @param int $max_files
     * @return array|bool
     */
    public function upload($upload_to, $max_files = -1)
    {
        if ( !is_dir($upload_to) && !mkdir($upload_to, 0777, true) ) {
            return false;
        }

        $upload_to = rtrim($upload_to, S) . S;
        $result = [];
        foreach ( $this->files as $file ) {
            // Get file data from request
            $data = isset($_FILES[$file]) ? $_FILES[$file] : null;
            if ( !$data ) {
                $result[$file] = ['empty'];
            }

            // Check request errors
            if ( isset($data['error']) && $data['error'] ) {
                $result[$file] = [$data['error']];
                continue;
            }

            // Check filesize
            if ( $data['size'] > $this->max_filesize ) {
                $result[$file] = ['big', $this->max_filesize];
                continue;
            }

            // Check extension
            if ( !$this->allowed_extension($data['name']) ) {
                $result[$file] = ['extension', implode(', ', $this->extensions)];
                continue;
            }

            // Upload file
            $name = is_null($this->name_handler) ? $data['name'] : call_user_func($this->name_handler, $data['name']);
            $dest = $upload_to . $name;
            if ( move_uploaded_file($data['tmp_name'], $dest) ) {
                $result[$file] = ['uploaded', $data['name']];
            } else {
                $result[$file] = ['cantmove', $data['name']];
            }

            // Max files
            if ( $max_files > 0 && count($result) >= $max_files ) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param $filename
     * @return string
     */
    private function allowed_extension($filename)
    {
        $filename = explode('.', $filename);
        $ext = strtolower(end($filename));
        return in_array($ext, $this->extensions) || in_array('*', $this->extensions);
    }
} 