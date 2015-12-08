<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    public function urls()
    {
        $this->map->addPattern('<id:\d+?>/view/profile', [$this, 'profile'], 'user.profile');
    }

    public function profile(Request $request, $matches)
    {
        $this->view->twig->display('index.twig');
    }
} 