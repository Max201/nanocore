<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;

use Module\Admin\Helper;


class NCControl extends NCModule
{
    static $fa_icon = 'users';
    static $menu = [];

    /**
     * @param $url
     * @param $theme
     */
    public function __construct($url, $theme = 'default')
    {
        // Set default admin theme & Namespace
        parent::__construct($url, 'admin', 'control');
    }

    /**
     * @return bool
     */
    public function access()
    {
        return $this->user ? $this->user->can('use_admin') : false;
    }

    public function configure()
    {
        // Control panel menu
        $this->view->assign('menu', Helper::build_menu($this->lang));
    }
}