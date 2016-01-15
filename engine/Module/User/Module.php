<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Environment\Env;
use System\Environment\Options;
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
     * ULogin registration and authorization
     *
     * @param NCModule $module
     * @param \Service\Render\Theme $theme
     * @param \Service\Application\Translate $translate
     * @return array|\System\Engine\NCBlock[]
     */
    static function globalize($module, $theme, $translate)
    {
        if ( !$module->user && Env::$request->isMethod('post') && isset($_POST['token']) ) {
            $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
            $data = new Options(json_decode($s, true));
            $hash = User::encrypt(
                User::encrypt($data->get('network') . $data->get('identity'))
            );

            if ( $user = $module->auth->authenticate_by_hash($hash) ) {
                $module->auth->login($user);
            } else {
                $user = User::create([
                    'username'  => $data['first_name'] . ' ' . $data['last_name'],
                    'password'  => $hash,
                    'group'     => $module->settings->get('users_group')
                ]);

                if ( $user instanceof User ) {
                    $module->auth->login($user);
                }
            }
        }

        return [
            'ulogin'    => [
                'small' => '<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin" data-ulogin="display=small;fields=first_name,last_name;providers=twitter,facebook,youtube,googleplus,vkontakte;hidden=other;redirect_uri='.Env::$request->getSchemeAndHttpHost().'"></div>',
                'panel' => '<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin" data-ulogin="display=panel;fields=first_name,last_name;providers=twitter,facebook,youtube,googleplus,vkontakte;hidden=other;redirect_uri='.Env::$request->getSchemeAndHttpHost().'"></div>'
            ]
        ];
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
                'email'     => $request->request->get('email'),
                'group_id'  => $this->settings->get('users_group', \Group::first()->id)
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