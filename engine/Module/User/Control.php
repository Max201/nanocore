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
    public function urls()
    {
        $this->map->addRoute('home', [$this, 'test'], 'home');
    }

    public function test(Request $request, $matches)
    {
        return $this->view->render('base.twig', ['title'=>'hello']);
    }
} 