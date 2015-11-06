<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\VarBag;

class NCService
{
    /**
     * Config requires
     */
    const CONFIG = null;

    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @return self
     */
    public static function instance()
    {
        if ( is_null(static::$instance) ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param string $service_name
     * @param array $args
     * @return NCService
     */
    public static function load($service_name, array $args = [])
    {
        $class_name = '\\Service\\' . str_replace('.', '\\', $service_name);
        if ( class_exists($class_name) ) {
            return call_user_func_array($class_name . '::instance', $args);
        }

        return false;
    }

    /**
     * @param $config
     * @param bool $object
     * @return array|VarBag
     */
    public function config($config, $object = true)
    {
        if ( is_null(static::CONFIG) ) {
            user_error('Config is disabled for this service', E_USER_ERROR);
        }

        $path = ROOT . S . 'engine' . S . 'Service' . S . str_replace('.', S, static::CONFIG) . S . str_replace('.', S, $config) . '.json';
        if ( !file_exists($path) ) {
            user_error('Config ' . $path . ' does not exists', E_USER_ERROR);
        }

        $config = json_decode(file_get_contents($path), !$object);
        if ( $object ) {
            return new VarBag($config);
        }

        return $config;
    }

    /**
     * @param $config
     * @param array $data
     * @return mixed
     */
    public function update_config($config, array $data = [])
    {
        if ( is_null(static::CONFIG) ) {
            user_error('Config is disabled for this service', E_USER_ERROR);
        }

        $path = ROOT . S . 'engine' . S . 'Service' . S . str_replace('.', S, static::CONFIG) . S . str_replace('.', S, $config) . '.json';
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
} 