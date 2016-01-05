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
use System\Util\FileUploader;


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

        $this->map->addRoute('files', [$this, 'fmanager'], 'filemanager');
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

    public function fmanager(Request $request)
    {
        $root = ROOT . S . 'static';
        $method = strtolower($request->get('m', 'list'));
        $dir = $root . $request->get('d', S);

        // Assign current directory path
        $this->view->assign('dir', $request->get('d', S));

        // Assign prev directory path
        $up_dir = explode(S, $request->get('d', S));
        array_pop($up_dir);
        $up_dir = implode(S, $up_dir);
        $this->view->assign('up', $up_dir ? $up_dir : S);

        // Base URL for any item
        $url = '/static/' . str_replace(S, '/', trim($request->get('d', S), S));
        $this->view->assign('base_url', $url);

        // Define wyi name
        $this->view->assign('wyi', $request->get('wyi', 'edit'));
        switch ( $method ) {
            // Delete file
            case 'delete':
                $filename = rtrim($dir, S) . S . $request->get('f');
                if ( file_exists($filename) ) {
                    Helper::delete($filename);
                }

                break;

            // Upload file
            case 'upload':
                $uploader = new FileUploader(['file']);
                $r = $uploader->upload($dir);
                var_dump($r);
                break;

            // Rename file
            case 'rename':
                $filename = rtrim($dir, S) . S . $request->get('f');
                $newname = rtrim($dir, S) . S . $request->get('n');
                if ( file_exists($newname) ) {
                    Helper::delete($newname);
                }

                if ( file_exists($filename) ) {
                    rename($filename, $newname);
                }

                break;

            // Create folder
            case 'create':
                $dirname = rtrim($dir, S) . S . $request->get('f');
                if ( !file_exists($dirname) && $request->get('f', false) ) {
                    @mkdir($dirname, 0777, true);
                }

                break;

            default: break;
        }

        return $this->view->render('com/filemanager.twig', [
            'items' => Helper::items($dir, ['.', '..'])
        ]);
    }
} 