<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\User;


use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class Permissions
 * @package Service\User
 */
class Permissions extends NCService
{
    const CONFIG = 'User.config';

    /**
     * @var Permissions
     */
    static $instance;

    /**
     * @var Options
     */
    public $map;

    /**
     * Load permissions to manage
     */
    public function __construct()
    {
        $this->map = $this->config('permissions');
    }

    /**
     * @return bool
     */
    public function save()
    {
        return $this->update_config('permissions', $this->map->getArrayCopy());
    }
} 