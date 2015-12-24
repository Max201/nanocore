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
        'user.list' => '/control/user/',
        'user.groups' => '/control/user/groups/',
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'users_list'], 'home');
    }

    public function users_list(Request $request, $matches)
    {
        $users = \User::all();

        $users = array_map(function($i){ return $i->asArrayFull(); }, $users);
        return $this->view->render('users/list.twig', [
            'title'         => $this->lang->translate('user.list'),
            'users_list'    => $users
        ]);
    }
} 