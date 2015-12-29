<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use Service\Application\Translate;
use System\Engine\NCService;
use System\Engine\NCModule;
use Service\Render\Theme;
use User;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addRoute('new', [$this, 'registration'], 'user.new');
    }

    /**
     * User registration page
     */
    public function registration(Request $request, $matches)
    {
        if ( $request->isMethod('post') ) {
            $user = new User([
                'username'  => $request->request->get('username'),
                'password'  => $request->request->get('password'),
                'email'  => $request->request->get('email'),
            ]);

            if ( !$user->save(true) ) {
                $this->view->twig->addGlobal('errors', ['Undefined error']);
            }
        }

        return $this->view->twig->render('user/registration.twig');
    }
} 