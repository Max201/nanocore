<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCService;
use System\Environment\Options;


class Settings extends NCService
{
    const CONFIG = 'Application.config';

    /**
     * @var Settings
     */
    static $instance;

    /**
     * @var Options
     */
    public $conf;

    /**
     * System settings manager service
     */
    public function __construct()
    {
        $this->conf = $this->config('application');
    }

    /**
     * @return bool
     */
    public function save()
    {
        return $this->update_config('application', $this->conf->getArrayCopy());
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return $this->conf->get($key, $default);
    }

    /**
     * @param $key
     * @param null $value
     */
    public function set($key, $value = null)
    {
        $this->conf->set($key, $value);
    }

    /**
     * @return Settings
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Settings();
        }

        return static::$instance;
    }
} 