<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Service\Paginator\Listing;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCControl;
use System\Engine\NCService;
use System\Environment\Arguments;
use System\Environment\Env;


class Control extends NCControl
{
    static $menu = [
        'user.list' => '/control/user/',
        'user.groups' => '/control/user/groups/',
    ];

    public function route()
    {
        // Users
        $this->map->addRoute('/', [$this, 'users_list'], 'users');
        $this->map->addRoute('create', [$this, 'create_user'], 'users.create');
        $this->map->addPattern('profile/<id:\d+?>', [$this, 'profile'], 'users.profile');

        // Groups
        $this->map->addRoute('groups', [$this, 'groups_list'], 'groups');
        $this->map->addRoute('groups/create', [$this, 'create_group'], 'groups.create');

        // Settings
        $this->map->addRoute('settings', [$this, 'users_settings'], 'settings');
    }

    public function profile(Request $request, $matches)
    {
        try {
            $user = \User::find($matches['id']);
        } catch ( \Exception $e ) {
            $this->error404($request);
            return;
        }

        if ( $request->isMethod('post') ) {
            $new_password = $request->get('new_password');
            if ( $new_password ) {
                Env::$response->headers->set('Content-Type', 'application/json');

                $user->password = $new_password;
                if ( $user->save() ) {
                    return json_encode([
                        'status'    => $this->lang->translate('form.saved'),
                        'code'      => 'success'
                    ]);
                } else {
                    return json_encode([
                        'status'    => $this->lang->translate('form.failed'),
                        'code'      => 'error'
                    ]);
                }
            }
        }

        return $this->view->render('users/profile.twig', [
            'title'         => $this->lang->translate('user.profile.name', $user->username),
            'profile'       => $user->asArrayFull(),
            'groups'        => array_map(function($i){ return $i->to_array(); }, \Group::all())
        ]);
    }

    public function groups_list(Request $request)
    {
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->get('page', 1), \Group::count()]);
        $filter = $paginator->limit();

        // Filter groups
        $groups = \Group::all($filter);
        $groups = array_map(function($i){ return $i->to_array(); }, $groups);
        return $this->view->render('users/groups.twig', [
            'title'         => $this->lang->translate('user.groups'),
            'groups_list'    => $groups,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function users_list(Request $request, $matches)
    {
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->get('page', 1), \User::count()]);
        $filter = $paginator->limit();

        // Filter users
        $users = \User::all($filter);
        $users = array_map(function($i){ return $i->asArrayFull(); }, $users);
        return $this->view->render('users/list.twig', [
            'title'         => $this->lang->translate('user.list'),
            'users_list'    => $users,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }
} 