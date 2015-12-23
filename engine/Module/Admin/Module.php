<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


use Service\User\Auth;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCControl;
use System\Engine\NCService;
use System\Environment\Env;


class Module extends NCControl
{
    public function route()
    {
        // Disabling namespace
        $this->map->setNameSpace();

        // Authentication
        $this->map->addRoute('login', [$this, 'login'], 'login');

        // Dashboard
        $this->map->addRoute('/', [$this, 'dashboard'], 'dashboard');
    }

    public function access()
    {
        if ( is_null($this->user) ) {
            if ( Env::$request->server->get('REQUEST_URI') == '/admin/login' ) {
                return true;
            } else {
                header('Location: ' . $this->map->reverse('login'));
                die;
            }
        }

        return parent::access();
    }

    public function dashboard()
    {
        return $this->view->render('dashboard/index.twig', [
            'title' => 'Dashboard'
        ]);
    }

    public function login(Request $request)
    {
        if ( $request->isMethod('POST') ) {
            Env::$response->headers->set('Content-Type', 'application/json', true);

            /** @var Auth $service */
            $service = NCService::load('User.Auth');
            $user = $service->authenticate($request->get('username'), $request->get('password'));
            if ( $user->can('access') ) {
                $service->login($user);
                return json_encode(['status' => 'ok']);
            } else {
                return json_encode(['error' => 'failed']);
            }
        }

        return $this->view->render('users/login.twig', [
            'title' => 'Authorization'
        ]);
    }
} 