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
        'admin.services'    => '/admin/services/',
        'admin.settings'    => '/admin/settings/',
        'admin.packer'      => '/admin/packer/',
    ];
} 