<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\Options;


/**
 * Class NCService
 * @package System\Engine
 */
class NCService
{
    /**
     * Config requires
     */
    const CONFIG = null;

    /**
     * @param string $service_name
     * @param array $args
     * @return NCService
     */
    public static function load($service_name, array $args = [])
    {
        $class_name = '\\Service\\' . str_replace('.', '\\', $service_name);
        if ( !class_exists($class_name) ) {
            $class = explode('.', $service_name);
            $class_name = '\\Service\\' . str_replace('.', '\\', $service_name . '.' . end($class));
        }

        if ( class_exists($class_name) ) {
            return call_user_func_array($class_name . '::instance', $args);
        }

        return false;
    }

    /**
     * @param $config
     * @return Options
     */
    public static function config($config)
    {
        if ( is_null(static::CONFIG) ) {
            user_error('Config is disabled for this service', E_USER_ERROR);
        }

        $path = ROOT . S . 'engine' . S . 'Service' . S . str_replace('.', S, static::CONFIG) . S . str_replace('.', S, $config) . '.json';
        if ( !file_exists($path) ) {
            user_error('Config ' . $path . ' does not exists', E_USER_ERROR);
        }

        $result = [];
        $file = file_get_contents($path);
        if ( $file && strlen($file) > 2 ) {
            $result = json_decode($file, true);
        }

        return new Options((array)$result);
    }

    /**
     * @param $config
     * @param array $data
     * @return mixed
     */
    public static function update_config($config, array $data = [])
    {
        if ( is_null(static::CONFIG) ) {
            user_error('Config is disabled for this service', E_USER_ERROR);
        }

        $path = ROOT . S . 'engine' . S . 'Service' . S . str_replace('.', S, static::CONFIG) . S . str_replace('.', S, $config) . '.json';
        return file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
} 