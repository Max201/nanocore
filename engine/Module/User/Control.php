<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCControl;


class Control extends NCControl
{
    static $menu = [
        'users' => '/control/user/'
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'users_list'], 'home');
    }

    public function users_list(Request $request, $matches)
    {
        return $this->view->render('dashboard/index.twig', [
            'title' => 'Users List'
        ]);
    }
} 