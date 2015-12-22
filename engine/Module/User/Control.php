<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;


class Control extends NCModule
{
    public function urls()
    {
        $this->map->addRoute('home', [$this, 'test'], 'home');
    }

    public function test(Request $request, $matches)
    {
        return $this->view->render('@admin/base.twig', ['title'=>'hello']);
    }
} 