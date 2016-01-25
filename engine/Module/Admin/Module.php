<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


use Module\Admin\SMP\Driver;
use Service\Application\Translate;
use Service\Render\Theme;
use Service\SocialMedia\SocialMedia;
use Service\SocialMedia\Vkontakte;
use Symfony\Component\HttpFoundation\Request;
use Service\Application\Settings;
use System\Engine\NCControl;
use System\Engine\NCModule;
use System\Engine\NCModuleCore;
use System\Engine\NCService;
use System\Environment\Env;
use Service\User\Auth;
use System\Environment\Options;
use System\Util\Calendar;
use System\Util\FileUploader;


class Module extends NCControl
{
    /**
     * Disable analytics
     */
    public $analytics = false;


    public function route()
    {
        // Disabling namespace
        $this->map->setNameSpace();
        // Admin Menu
        $this->view->assign('menu', Helper::build_menu($this->lang));

        // Routes
        $this->map->addRoute('/', [$this, 'dashboard'], 'dashboard');
        $this->map->addRoute('login', [$this, 'login'], 'login');
        $this->map->addRoute('logout', [$this, 'logout'], 'logout');
        $this->map->addRoute('settings', [$this, 'settings'], 'settings');
        $this->map->addRoute('services', [$this, 'services'], 'services');
        $this->map->addRoute('modules', [$this, 'modules'], 'modules');
        $this->map->addRoute('social-posting', [$this, 'smp'], 'smp');

        $this->map->addPattern('stats/<day:\d+?>', [$this, 'stats'], 'stats');
        $this->map->addPattern('stats/<day:\d+?>/<method:\w+?>', [$this, 'stats'], 'mstats');
        $this->map->addPattern('social-posting/<drv:\w+?>', [$this, 'smp'], 'smpp');

        $this->map->addRoute('files', [$this, 'fmanager'], 'filemanager');
    }

    static function globalize(NCModule $module, Theme $view, Translate $lang)
    {
        $view->twig->addFilter(new \Twig_SimpleFilter('ord', function($order){
            $cur = Env::$request->get('order');
            if ( strpos($cur, $order) > -1 ) {
                return $cur[0] == '-' ? 'fa-chevron-down' : 'fa-chevron-up';
            }

            return strpos($cur, $order);
        }));


        $view->twig->addFilter(new \Twig_SimpleFilter('sub', function($str, $len = 24){
            if ( mb_strlen($str) > $len ) {
                $str = mb_substr($str, 0, $len - 3) . '...';
            }

            return $str;
        }));

        return [
            'title_prefix'  => NCService::load('Application.Settings')->conf->get('title_prefix'),
            'lang_code'     => $lang->pack
        ];
    }

    public function access()
    {
        if ( is_null($this->user) ) {
            if ( Env::$request->server->get('REQUEST_URI') == '/admin/login' ) {
                return true;
            } else {
                header('Location: /admin/login');
                die;
            }
        }

        return parent::access();
    }

    public function stats(Request $request, Options $matches = null)
    {
        $cur_day = $matches->get('day', date('d'));
        if ( $cur_day > date('d') ) {
            return static::redirect_response($this->map->reverse('stats', [date('d')]));
        }

        $statistic = [];
        $start_date = mktime(0, 0, 0, date('m'), $cur_day);
        $end_date = strtotime('+1 day', $start_date);

        // Online users
        $statistic['online'] = \Visit::online();

        // Visits
        $statistic['visits'] = \Visit::visits($start_date, $end_date);

        // Views
        $statistic['views'] = \Visit::views($start_date, $end_date);

        // Search terms
        $statistic['terms'] = \Visit::query_terms($start_date, $end_date);

        // Websites
        $statistic['websites'] = \Visit::websites($start_date, $end_date);

        // Pages
        $statistic['pages'] = \Visit::pages($start_date, $end_date);

        // Browsers
        $statistic['browsers'] = \Visit::browsers($start_date, $end_date);

        // Platforms
        $statistic['platforms'] = \Visit::platforms($start_date, $end_date);

        return $this->view->render('dashboard/stats.twig', [
            'day'       => $cur_day,
            'stat'      => $statistic,
            'title'     => $this->lang->translate(
                'admin.statistic.title',
                $this->lang->translate_date(
                    date('D, d M Y', strtotime($start_date))
                )
            )
        ]);
    }

    public function smp(Request $request, Options $matches = null)
    {
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');

        // Selected network
        $soc = $smp->network($request->get('soc') ? $request->get('soc') : $matches->get('drv'));
        if ( $soc || ($matches && $matches->get('drv')) ) {
            $message = '';
            $status = '';
            $manager = $smp->get_manager($soc['id']);
            $redirect_uri = $manager::$redirect_uri ? $manager::$redirect_uri : $this->map->reverse('smpp', $soc['id']);

            // If saving settings
            if ( $request->isMethod('post') ) {
                if ( $request->isMethod('post') && $manager->setup($_POST) ) {
                    $soc = $smp->network($request->get('soc'));
                    $status = 'success';
                    $message = $this->lang->translate('form.saved');
                } else {
                    $status = 'error';
                    $message = $this->lang->translate('form.failed');
                }
            }

            // Getting token
            if ( $matches && $matches->get('drv') ) {
                if ( Driver::process($soc['id'], [$redirect_uri]) ) {
                    $status = 'success';
                    $message = $this->lang->translate('form.authorized');
                } else {
                    $status = 'error';
                    $message = $this->lang->translate('form.failed');
                }
            }

            // VK Confirmation system
            if ( $manager instanceof Vkontakte && $manager->active() ) {
                $groups = $manager->request('groups.get', [
                    'user_id'       => $manager->conf->get('user_id'),
                    'filter'        => 'admin',
                    'extended'      => 1,
                ]);

                if ( array_key_exists('error', $groups) ) {
                    $status = 'error';
                    if ( $groups['error']['error_code'] == 17 ) {
                        $message = $groups['error']['error_msg'] . ' - <a href="' . $groups['error']['redirect_uri'] . '" target="_blank">Fix this issue</a>';
                    } else {
                        $message = $groups['error']['error_msg'];
                    }
                }
            }

            return $this->view->render('com/smp/' . $soc['id'] . '.twig', [
                'title'     => $this->lang->translate('admin.smp.setup', $soc['name']),
                'network'   => $soc,
                'status'    => $status,
                'message'   => $message,
                'auth_url'  => $manager->configured() ? $manager->authorize_url($redirect_uri) : false
            ]);
        }

        return $this->view->render('dashboard/smp.twig', [
            'title'     => $this->lang->translate('admin.smp.title'),
            'socials'   => $smp->social_list()
        ]);
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
            'home'      => $request->server->get('SERVER_NAME'),
            'groups'    => array_map(function($i){ return $i->to_array(); }, \Group::all()),
        ]);
    }

    public function dashboard(Request $request)
    {
        $cur_day = date('d');
        $statistic = [];
        $start_date = mktime(0, 0, 0, date('m'), $cur_day);
        $end_date = strtotime('+1 day', $start_date);

        // Online users
        $statistic['online'] = \Visit::online();

        // Visits
        $statistic['visits'] = \Visit::visits($start_date, $end_date);

        // Views
        $statistic['views'] = \Visit::views($start_date, $end_date);

        // Search terms
        $statistic['terms'] = \Visit::query_terms($start_date, $end_date);

        // Websites
        $statistic['websites'] = \Visit::websites($start_date, $end_date);

        // Pages
        $statistic['pages'] = \Visit::pages($start_date, $end_date);

        // Browsers
        $statistic['browsers'] = \Visit::browsers($start_date, $end_date);

        // Platforms
        $statistic['platforms'] = \Visit::platforms($start_date, $end_date);

        return $this->view->render('dashboard/index.twig', [
            'title'     => $this->lang->translate('admin.dashboard'),
            'calendar'  => new Calendar(),
            'month'     => $this->lang->translate('system.month.' . strtolower(date('M'))),
            'active'    => date('d'),
            'day'       => $cur_day,
            'stat'      => $statistic,
            'widgets'   => Helper::build_widgets($this->view)
        ]);
    }

    public function login(Request $request)
    {
        if ( $request->isMethod('POST') ) {
            if ( !NCModuleCore::verify_captcha($request->get('captcha')) ) {
                return static::json_response(['error' => 'failed']);
            }

            /** @var Auth $service */
            $service = NCService::load('User.Auth');
            $user = $service->authenticate($request->get('username'), $request->get('password'));
            if ( $user && $user->can('access') ) {
                $service->login($user);
                return static::json_response(['status' => 'ok']);
            } else {
                return static::json_response(['error' => 'failed']);
            }
        }

        return $this->view->render('users/login.twig', [
            'title' => 'Authorization'
        ]);
    }

    public function logout(Request $request)
    {
        /** @var Auth $service */
        $service = NCService::load('User.Auth');
        $service->logout();

        Env::$response->sendHeaders();
        header('Location: ' . $this->map->reverse('login'));
        die;
    }

    public function fmanager(Request $request)
    {
        $root = ROOT . S . 'static';
        $method = strtolower($request->get('m', 'list'));
        $dir = $root . $request->get('d', S);
        function filterp($path) { return str_replace(S . S, S, str_replace('//', '/', $path)); }
        function filterup($path) { $path = explode(S, $path); array_pop($path); return implode(S, $path); }

        // Assign current directory path
        $this->view->assign('dir', rtrim($request->get('d', S), S) . S);

        // Assign prev directory path
        $up_dir = filterup($request->get('d', S));
        $this->view->assign('up', $up_dir ? $up_dir : S);

        // Base URL for any item
        $url = filterp('/static/' . trim(str_replace(S, '/', $request->get('d', S)), '/') . '/');
        $this->view->assign('base_url', $url);

        // Define wyi name
        $this->view->assign('wyi', $request->get('wyi', 'edit'));
        switch ( $method ) {
            // Delete file
            case 'delete':
                $filename = filterp(rtrim($dir, S) . S . $request->get('f'));
                if ( file_exists($filename) ) {
                    Helper::delete($filename);
                }

                break;

            // Upload file
            case 'upload':
                $uploader = new FileUploader(['file']);
                $uploader->upload($dir);
                break;

            // Rename file
            case 'rename':
                $filename = filterp(rtrim($dir, S) . S . $request->get('f'));
                $newname = filterp(rtrim($dir, S) . S . $request->get('n'));
                if ( file_exists($newname) ) {
                    Helper::delete($newname);
                }

                if ( file_exists($filename) ) {
                    rename($filename, $newname);
                    $this->view->assign('up', filterup($up_dir));
                }

                break;

            // Create folder
            case 'create':
                $dirname = filterp(rtrim($dir, S) . S . $request->get('f'));
                if ( !file_exists($dirname) && $request->get('f', false) ) {
                    @mkdir($dirname, 0777, true);
                }

                break;

            default: break;
        }

        return $this->view->render('com/filemanager.twig', [
            'items' => Helper::items($dir, ['.', '..']),
            'date'  => date('Y-m-d')
        ]);
    }
} 