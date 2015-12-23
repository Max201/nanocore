<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCService;


/**
 * Class Translate
 * @package System\Middleware
 */
class Translate extends NCService
{
    const CONFIG = 'Application.config';

    /**
     * @var array
     */
    private $_cache = array();

    /**
     * @var string
     */
    private $_root;

    /**
     * @var Translate
     */
    static $instance;

    /**
     * Create translate object
     */
    private function __construct()
    {
        $this->_root = ROOT . S . 'engine' . S . 'Language' . S . $this->config('application')->get('lang');
        if ( is_dir($this->_root) ) {
            $this->_loadCache();
        }
    }

    /**
     * @param string $string
     * @return string
     */
    public function e($string)
    {
        $args = func_get_args();

        if ( array_key_exists($string, $this->_cache) ) {
            $args[0] = $this->_cache[$string];
            return call_user_func_array( 'sprintf', $args );
        }

        $this->_reloadTranslate();
        $this->_writeCache();

        if ( array_key_exists($string, $this->_cache) ) {
            $args[0] = $this->_cache[$string];
            return call_user_func_array( 'sprintf', $args );
        }

        return $this->_noTranslate($string);
    }

    /**
     * @param $string
     * @return mixed
     */
    private function _noTranslate($string)
    {
        return $string;
    }

    /**
     * Загружает перевод с кеша
     */
    private function _loadCache()
    {
        $path = ROOT . S . 'Service' . S . 'Application' . S . 'cache' . S . 'translate.php';
        if ( file_exists($path) ) {
            $this->_cache = include $path;
        }
    }

    /**
     * Сохраняет перевод в кеш
     */
    private function _writeCache()
    {
        $content = '<?php return ' . var_export($this->_cache, true) . ';';
        file_put_contents(
            ROOT . S . 'Service' . S . 'Application' . S . 'cache' . S . 'translate.php',
            $content
        );
    }

    /**
     * @param null $dir
     */
    private function _reloadTranslate($dir = null)
    {
        if ( is_null($dir) ) {
            $this->_cache = array();
        }

        $dir = is_null($dir) ? $this->_root : $dir;
        $dirHandle = opendir($dir);

        while ( $item = readdir($dirHandle) ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }

            $path = $dir . S . $item;
            if ( is_dir($path) ) {
                $this->_reloadTranslate($path);
            } else {
                $this->_parseArray($this->_loadJson($path));
            }
        }
    }

    /**
     * @param array $array
     * @param string $parent
     */
    private function _parseArray($array = array(), $parent = '')
    {
        foreach ( $array as $key => $value ) {
            if ( is_string($value) ) {
                $this->_cache[$parent . $key] = $value;
                continue;
            }

            if ( is_array($value) ) {
                $childOf = $parent ? $parent . $key . '.' : $key . '.';
                $this->_parseArray($value, $childOf);
            }
        }
    }

    /**
     * @param $filename
     * @return mixed
     */
    private function _loadJson($filename)
    {
        return json_decode(file_get_contents($filename), true);
    }
} 