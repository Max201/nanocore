<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use User;
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