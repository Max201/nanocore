<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


use System\Engine\NCControl;


class Control extends NCControl
{
    static $fa_icon = 'dashboard';
    static $menu = [
        'admin.dashboard'   => '/admin/',
        'admin.settings'    => '/admin/settings/',
        'admin.services'    => '/admin/services/',
        'admin.modules'      => '/admin/modules/',
    ];
} 