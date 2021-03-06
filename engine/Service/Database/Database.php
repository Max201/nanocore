<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:24
 */

namespace Service\Database;


use ActiveRecord\Config;
use ActiveRecord\Connection;
use ActiveRecord\ConnectionManager;
use System\Engine\NCService;


class Database extends NCService
{
    const CONFIG = 'Database.config';

    static $instance;

    /**
     * @param null $default
     * @return bool
     */
    public static function instance($default = null)
    {
        if ( !static::$instance ) {
            $conf = self::config('database');
            Config::initialize(function($set) use($conf, $default) {
                $set->set_model_directory(ROOT . S . $conf->get('models', 'engine/Models'));
                $set->set_connections(
                    $conf->get('connections'),
                    !is_null($default) ? $default : $conf->get('default_connection', 'default')
                );
            });

            static::$instance = true;
        }

        return static::$instance;
    }

    /**
     * @param string $connection
     * @return Connection
     */
    public static function connection($connection = null)
    {
        if ( is_null($connection) ) {
            $connection = static::config('database')->get('default_connection', 'default');
        }

        return ConnectionManager::get_connection($connection);
    }
}