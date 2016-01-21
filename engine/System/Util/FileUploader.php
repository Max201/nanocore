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
     * @var array
     */
    private $last_uploads = [];

    /**
     * @var bool
     */
    private $replace_mode = true;

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
     * @param bool $on
     * @return bool
     */
    public function replace_mode($on = null)
    {
        if ( is_null($on) ) {
            return $this->replace_mode;
        }

        $this->replace_mode = $on;
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
                $result[$file] = ['big', round($this->max_filesize / (1024 * 1024), 2)];
                continue;
            }

            // Check extension
            if ( !$this->allowed_extension($data['name']) ) {
                $result[$file] = ['extension', implode(', ', $this->extensions)];
                continue;
            }

            // Upload file name
            $data['name'] = is_null($this->name_handler) ?
                $data['name'] :
                call_user_func(
                    $this->name_handler,
                    $data['name'],
                    $this->get_extension($data['name'])
                );
            $dest = $upload_to . $data['name'];

            // If exists
            if ( file_exists($dest) || $this->replace_mode ) {
                @unlink($dest);
            } else {
                $result[$file] = ['exists', $data['name']];
                continue;
            }

            // Upload file
            if ( move_uploaded_file($data['tmp_name'], $dest) ) {
                $result[$file] = ['uploaded', $data['name'], $dest];
            } else {
                $result[$file] = ['cantmove', $data['name']];
            }

            // Max files
            if ( $max_files > 0 && count(array_keys($result)) >= $max_files ) {
                break;
            }
        }

        // Save latest uploads
        $this->last_uploads = $result;

        return $result;
    }

    /**
     * @param $field
     * @return bool
     */
    public function is_uploaded($field)
    {
        return array_key_exists($field, $this->last_uploads) && $this->last_uploads[$field][0] == 'uploaded';
    }

    /**
     * @param $field
     * @return string
     */
    public function get_name($field)
    {
        return $this->is_uploaded($field) ? $this->last_uploads[$field][1] : null;
    }

    /**
     * @param $field
     * @param $max_width
     * @param $max_height
     * @return bool
     */
    public function resize($field, $max_width, $max_height)
    {
        if ( !class_exists('\\Imagick') || !isset($this->last_uploads[$field][2]) ) {
            return true;
        }

        $img_path = $this->last_uploads[$field][2];
        $img = new \Imagick($img_path);

        $w = $img->getimagewidth();
        $h = $img->getimageheight();
        $nw = $w;
        $nh = $h;

        if ( $w > $max_width ) {
            $nw = $max_width;
        }

        if ( $h > $max_height ) {
            $nh = $max_height;
        }

        if ( $nw != $w || $nh != $h ) {
            $img->resizeImage($nw, $nh, \Imagick::FILTER_LANCZOS, 1);
        } else {
            return true;
        }

        return $img->writeimage($img_path) && $img->destroy();
    }

    /**
     * @param $filename
     * @return string
     */
    private function get_extension($filename)
    {
        $filename = explode('.', $filename);
        return strtolower(end($filename));
    }

    /**
     * @param $filename
     * @return string
     */
    private function allowed_extension($filename)
    {
        $ext = $this->get_extension($filename);
        return in_array($ext, $this->extensions) || in_array('*', $this->extensions);
    }
} 