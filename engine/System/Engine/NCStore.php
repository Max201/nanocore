<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


/**
 * Class NCStore
 * @package System\Engine
 */
class NCStore
{
    /**
     * Storage file
     *
     * @var string
     */
    static $storage = 'storage.dat';

    /**
     * @var NCStore
     */
    private static $instance;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $storage_path;

    /**
     * Create new store instance
     */
    public function __construct()
    {
        $this->storage_path = ROOT . S . 'engine' . S . static::$storage;

        // Load data
        if ( file_exists($this->storage_path) ) {
            $this->load();
        }
    }

    /**
     * Automatic storing data
     */
    public function __destruct()
    {
        $this->store();
    }

    /**
     * @return NCStore
     */
    public static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new NCStore();
        }

        return static::$instance;
    }

    /**
     * Setting new value
     *
     * @param $name
     * @param $value
     * @param $time
     */
    public function set($name, $value, $time = 30)
    {
        $this->data[$name] = [
            'value' => $value,
            'expires' => time() + $time
        ];
    }

    /**
     * @param $name
     * @param int $time
     * @param $getter
     * @return mixed
     */
    public function get($name, $time = 30, $getter = null)
    {
        if ( is_callable($getter) ) {
            // If not yet stored
            if ( !array_key_exists($name, $this->data) ) {
                $this->set($name, call_user_func($getter), $time);
            }

            // If expired
            if ( $this->data[$name]['expires'] < time() ) {
                $this->set($name, call_user_func($getter), $time);
            }
        }

        return array_key_exists($name, $this->data) ? $this->data[$name]['value'] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function clear($name)
    {
        if ( array_key_exists($name, $this->data) ) {
            unset($this->data[$name]);
            return $this->store();
        }

        return true;
    }

    /**
     * Storing data
     *
     * @return int
     */
    private function store()
    {
        return file_put_contents($this->storage_path, $this->dump());
    }

    /**
     * Loading data
     */
    private function load()
    {
        $this->data = include $this->storage_path;
    }

    /**
     * @return string
     */
    private function dump()
    {
        return '<?php return ' . var_export($this->data, true) . ';';
    }
} 