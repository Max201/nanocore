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
} 