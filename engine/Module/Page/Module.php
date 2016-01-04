<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Page;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Util\Calendar;
use User;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addRoute('page/<id:\d+>/<slug:.+>/', [$this, 'page'], 'page');
    }

    /**
     * User registration page
     */
    public function page(Request $request, $matches)
    {


        return $this->view->twig->render('user/registration.twig');
    }
} 