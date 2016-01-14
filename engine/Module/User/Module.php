<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
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
        $this->map->addRoute('auth', [$this, 'login'], 'user.login');
    }

    /**
     * Authentication user
     */
    public function login(Request $request, $matches)
    {
        if ( $request->isMethod('post') ) {
            $errors = [];
            $username = $request->get('username');
            $password = $request->get('password');

            $user = $this->auth->authenticate($username, $password);
            if ( !($user instanceof User) ) {
                $errors[] = $this->lang->translate('user.auth.failed');
            } else {
                $this->auth->login($user);
            }

            if ( $errors ) {
                $this->view->assign('errors', $errors);
            } else {
                return static::redirect_response('/');
            }
        }

        return $this->view->render('user/login.twig', [
            'title'     => $this->lang->translate('user.auth.title')
        ]);
    }

    /**
     * User registration page
     */
    public function registration(Request $request, $matches)
    {
        $data = [];

        if ( $request->isMethod('post') ) {
            $errors = [];
            $data = [
                'username'  => $request->request->get('username'),
                'password'  => $request->request->get('password'),
                'email'  => $request->request->get('email'),
            ];

            // Create user instance
            $user = new User($data);

            // Validate password
            if ( strlen($user->password) < 6 ) {
                $errors[] = $this->lang->translate('user.edit.short_password');
            }

            // Validate username
            if ( strlen($user->username) < 4 ) {
                $errors[] = $this->lang->translate('user.edit.short_username');
            }

            if ( User::count(['conditions' => ['username = ?', $user->username]]) > 0 ) {
                $errors[] = $this->lang->translate('user.edit.exists', $user->username);
            }

            // Validate email
            if ( strlen($user->email) < 5 || strpos($user->email, '@') < 1 ) {
                $errors[] = $this->lang->translate('user.edit.wrong_email', $user->email);
            } elseif ( User::count(['conditions' => ['email = ?', $user->email]]) > 0  ) {
                $errors[] = $this->lang->translate('user.edit.exists_email', $user->email);
            }

            if ( $errors ) {
                $this->view->assign('errors', $errors);
            } else {
                if ( !$user->save(true) ) {
                    $this->view->twig->addGlobal('errors', [$this->lang->translate('form.failed')]);
                } else {
                    $autenticated = $this->auth->authenticate($data['username'], $data['password']);
                    if ( $autenticated ) {
                        $this->auth->login($autenticated);
                        return static::redirect_response('/');
                    }
                }
            }
        }

        return $this->view->twig->render('user/registration.twig', [
            'title'     => $this->lang->translate('user.registration.title'),
            'data'      => $data,
        ]);
    }
} 