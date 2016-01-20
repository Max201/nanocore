<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;

use Module\Admin\Helper;
use System\Environment\Env;


class NCControl extends NCModule
{
    static $fa_icon = 'users';
    static $menu = [];

    /**
     * @return NCWidget[]
     */
    static function widget()
    {
        return [];
    }

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
        if ( is_null($this->user) ) {
            if ( Env::$request->server->get('REQUEST_URI') == '/admin/login' ) {
                return true;
            } else {
                header('Location: /admin/login');
                die;
            }
        }

        return $this->user ? $this->user->can('use_admin') : false;
    }

    public function configure()
    {
        // Control panel menu
        $this->view->assign('menu', Helper::build_menu($this->lang));
    }
}