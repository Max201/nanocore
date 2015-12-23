<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Service\User\Auth;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    public function route()
    {
        $this->map->addRoute('home', [$this, 'test'], 'home');
    }

    public function test(Request $request, $matches)
    {
        var_dump($this->user);
    }
} 