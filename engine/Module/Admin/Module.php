<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


use Symfony\Component\HttpFoundation\Request;
use Service\Application\Settings;
use System\Engine\NCControl;
use System\Engine\NCService;
use System\Environment\Env;
use Service\User\Auth;
use System\Util\Calendar;


class Module extends NCControl
{
    public function route()
    {
        // Disabling namespace
        $this->map->setNameSpace();
        // Admin Menu
        $this->view->assign('menu', Helper::build_menu($this->lang));

        // Routes
        $this->map->addRoute('/', [$this, 'dashboard'], 'dashboard');
        $this->map->addRoute('login', [$this, 'login'], 'login');
        $this->map->addRoute('settings', [$this, 'settings'], 'settings');
        $this->map->addRoute('services', [$this, 'services'], 'services');
        $this->map->addRoute('modules', [$this, 'modules'], 'modules');
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

    public function modules(Request $request)
    {
        return $this->view->render('dashboard/modules.twig', [
            'title'     => $this->lang->translate('admin.modules'),
            'modules'   => NCService::load('Module')->modules_dict()
        ]);
    }

    public function services(Request $request)
    {
        $services = Helper::services();

        return $this->view->render('dashboard/services.twig', [
            'title'     => $this->lang->translate('admin.services'),
            'services'  => $services
        ]);
    }

    public function settings(Request $request)
    {
        /** @var Settings $app */
        $app = NCService::load('Application.Settings');

        if ( $request->isMethod('POST') ) {
            foreach ( $_POST as $key => $val ) {
                $app->conf[$key] = $val;
            }

            if ( $app->save() ) {
                $this->view->assign('message', $this->lang->translate('form.saved'));
                $this->view->assign('status', 'success');
            } else {
                $this->view->assign('message', $this->lang->translate('form.failed'));
                $this->view->assign('status', 'error');
            }
        }

        return $this->view->render('dashboard/settings.twig', [
            'title'     => $this->lang->translate('admin.settings'),
            'conf'      => $app->conf,
            'langs'     => Helper::languages(),
            'themes'    => Helper::themes(),
            'home'      => $request->server->get('SERVER_NAME')
        ]);
    }

    public function dashboard(Request $request)
    {
        return $this->view->render('dashboard/index.twig', [
            'title'     => $this->lang->translate('admin.dashboard'),
            'widgets'   => Helper::build_widgets($this->view),
            'calendar'  => new Calendar(),
            'month'     => $this->lang->translate('system.month.' . strtolower(date('M'))),
            'active'    => date('d')
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