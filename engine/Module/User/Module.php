<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCModuleCore;
use System\Environment\Env;
use System\Environment\Options;
use System\Util\FileUploader;
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
        $this->map->addRoute('exit', [$this, 'logout'], 'user.exit');
        $this->map->addRoute('avatar', [$this, 'avatar'], 'user.avatar');
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
        // Default avatar letters
        $theme->twig->addFilter(new \Twig_SimpleFilter('ava', function($uname){
            $uname = explode(' ', $uname, 2);
            if ( count($uname) == 2 ) {
                return strtoupper($uname[0][0] . $uname[1][0]);
            }

            return strtoupper($uname[0][0]);
        }));

        // Gravatar
        $theme->twig->addFilter(new \Twig_SimpleFilter('grava', function($user, $size=128, $default=null) {
            return User::get_gravatar_url($user['email'], $size, $default);
        }));

        // ULogin authentication
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
                    'email'     => $data->get('email', $data->get('identity')),
                    'avatar'    => $data->get('photo', ''),
                    'group_id'  => $module->settings->get('users_group')
                ]);

                if ( $user instanceof User ) {
                    $module->auth->login($user);
                    Env::$response->sendHeaders();
                    return static::redirect_response('/');
                }
            }
        }

        return [
            'ulogin'    => [
                'small' => '<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin" data-ulogin="display=small;fields=first_name,last_name,email,photo;providers=twitter,facebook,youtube,googleplus,vkontakte;hidden=other;redirect_uri='.Env::$request->getSchemeAndHttpHost().'"></div>',
                'panel' => '<script src="//ulogin.ru/js/ulogin.js"></script><div id="uLogin" data-ulogin="display=panel;fields=first_name,last_name,email,photo;providers=twitter,facebook,youtube,googleplus,vkontakte;hidden=other;redirect_uri='.Env::$request->getSchemeAndHttpHost().'"></div>'
            ]
        ];
    }

    /**
     * Authentication user
     */
    public function login(Request $request, $matches)
    {
        $this->guest_only();

        $data = [];

        // Login
        if ( $request->isMethod('post') ) {
            $errors = [];
            $data = [
                'username'  => $request->get('username'),
                'password'  => $request->get('password'),
                'code'      => $request->get('code')
            ];

            // Authenticate user
            if ( NCModuleCore::verify_captcha($data['code']) ) {
                $user = $this->auth->authenticate($data['username'], $data['password']);
                if ( $user instanceof User ) {
                    $this->auth->login($user);
                } else {
                    $errors[] = $this->lang->translate('user.auth.failed');
                }
            } else {
                $errors[] = $this->lang->translate('user.auth.failed');
            }

            // Response
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
     * Logout user
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $this->auth->logout();
        return static::redirect_response($this->map->reverse('user.login'));
    }

    /**
     * Upload user avatar
     *
     * @param Request $request
     * @return mixed
     */
    public function avatar(Request $request)
    {
        $this->authenticated_only();

        // Avatar uploading settings
        $upload_to = ROOT . S . 'static' . S . 'user' . S . $this->user->id;
        $static_url = '/static/user/' . $this->user->id . '/';
        $allowed_formats = ['png', 'jpeg', 'jpg'];
        $max_size = 1; // MB
        $naming_handler = function ($filename, $ext) { return 'avatar.' . $ext; };
        $image_max = ['w' => 512, 'h' => 512];

        // Delete avatar
        if ( $request->get('delete') ) {
            if ( file_exists(ROOT . $this->user->avatar) ) {
                @unlink(ROOT . $this->user->avatar);
                $this->user->avatar = '';
                $this->user->save();
                return static::redirect_response($this->map->reverse('user.avatar'));
            }
        }

        // Uploadint avatar
        if ( $request->isMethod('post') ) {
            // Configure uploader
            $fileuploader = new FileUploader(['avatar'], $allowed_formats, $max_size);
            $fileuploader->set_naming_handler($naming_handler);
            $fileuploader->replace_mode(true);

            // Upload file
            $status = $fileuploader->upload($upload_to, 1)['avatar'];

            // Change user
            $old_file = $this->user->avatar;
            $this->user->avatar = $static_url . $fileuploader->get_name('avatar');

            // Response
            if ( $fileuploader->is_uploaded('avatar') && $this->user->save() && $fileuploader->resize('avatar', $image_max['w'], $image_max['h']) ) {
                if (file_exists(ROOT . $old_file) && $old_file != $this->user->avatar) @unlink(ROOT . $old_file);
                $this->view->assign('user', $this->user->to_array());
                $this->view->assign('message', $this->lang->translate('form.saved'));
            } else {
                $this->view->assign('error', $this->lang->translate('form.file.' . $status[0], $status[1]));
            }
        } else {
            $this->view->assign('message', $this->lang->translate('form.file.extension', implode(', ', $allowed_formats)));
        }

        return $this->view->render('user/avatar.twig', [
            'title'     => $this->lang->translate('user.profile.avatar_upload'),
            'max_size'  => $max_size,
            'formats'   => implode(', ', $allowed_formats),
        ]);
    }

    /**
     * User registration page
     */
    public function registration(Request $request, $matches)
    {
        $this->guest_only();

        $data = [];

        if ( $request->isMethod('post') ) {
            $errors = [];
            $captcha = $request->get('code');
            $data = [
                'username'  => $request->get('username'),
                'password'  => $request->get('password'),
                'email'     => $request->get('email'),
                'group_id'  => $this->settings->get('users_group', \Group::first()->id)
            ];

            // Create user instance
            $user = new User($data);

            // Check captcha
            if ( !NCModuleCore::verify_captcha($captcha) ) {
                $errors[] = $this->lang->translate('user.auth.code_wrong');
            }

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