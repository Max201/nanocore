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
    public function route()
    {
        $this->map->addRoute('dashboard', [$this, 'test'], 'home');
    }

    public function dashboard(Request $request, $matches)
    {
        return $this->view->render('dashboard/index.twig');
    }
} 