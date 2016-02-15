<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\User;


use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;
use System\Engine\NCWidget;


class Control extends NCControl
{
    /**
     * Control Panel menu
     *
     * @var array
     */
    static $menu = [
        [
            'title' => 'user.list',
            'href'  => '/control/user/',
            'counter'   => 'total_users',
        ],
        [
            'title' => 'user.groups',
            'href'  => '/control/user/groups/',
            'counter'   => 'total_groups',
        ],
    ];

    /**
     * Users counter
     *
     * @return int|string
     */
    static function total_users()
    {
        $last_day = \User::count([
            'conditions' => ['register_date > ?', mktime(0, 0, 0)]
        ]);

        if ( $last_day > 0 ) {
            return '+' . $last_day;
        }

        return \User::count();
    }

    /**
     * Groups counter
     *
     * @return int
     */
    static function total_groups()
    {
        return \Group::count();
    }

    /**
     * Module routing
     */
    public function route()
    {
        // Users
        $this->map->addRoute('/', [$this, 'users_list'], 'users');
        $this->map->addRoute('create', [$this, 'create_user'], 'users.create');
        $this->map->addPattern('profile/<id:\d+?>', [$this, 'profile'], 'users.profile');

        // Groups
        $this->map->addRoute('groups', [$this, 'groups_list'], 'groups');
        $this->map->addPattern('groups/profile/<id:\d+?>', [$this, 'group_profile'], 'groups.profile');
        $this->map->addRoute('groups/create', [$this, 'group_create'], 'groups.create');

        // Settings
        $this->map->addRoute('settings', [$this, 'users_settings'], 'settings');
    }

    /**
     * Create new user
     *
     * @param Request $request
     * @param null $matches
     * @return mixed|string
     */
    public function create_user(Request $request, $matches = null)
    {
        if ( $request->isMethod('post') ) {
            $errors = [];
            $data = [
                'username'  => $request->get('username'),
                'password'  => $request->get('password'),
                'email'     => $request->get('email'),
                'group_id'     => $request->get('group')
            ];

            $this->view->assign('data', $data);

            // Validate username
            if ( strlen($data['username']) < 4 ) {
                $errors[] = $this->lang->translate('user.edit.short_username');
            }

            if ( \User::find_by_username($data['username']) ) {
                $errors[] = $this->lang->translate('user.edit.exists', $data['username']);
            }

            // Validate password
            if ( strlen($data['password']) < 6 ) {
                $errors[] = $this->lang->translate('user.edit.short_password');
            }

            // Validate email
            if ( strpos($data['email'], '@') < 1 ) {
                $errors[] = $this->lang->translate('user.edit.wrong_email', $data['email']);
            }

            if ( \User::find_by_email($data['email']) ) {
                $errors[] = $this->lang->translate('user.edit.exists_email', $data['email']);
            }

            // Validate group
            if ( !\Group::find($data['group_id']) ) {
                $errors[] = $this->lang->translate('user.edit.wrong_group');
            }

            if ( count($errors) ) {
                return static::json_response([
                    'errors'    => implode('<br />', $errors),
                    'class'     => 'error'
                ]);
            } else {
                if ( $user = \User::create($data) ) {
                    return static::json_response([
                        'message'   => $this->lang->translate('form.saved'),
                        'class'     => 'success',
                        'user_id'   => $user->id
                    ]);
                } else {
                    return static::json_response([
                        'errors'    => $this->lang->translate('form.failed'),
                        'class'     => 'error'
                    ]);
                }
            }
        }

        return $this->view->render('users/create.twig', [
            'title'     => $this->lang->translate('user.create'),
            'groups'    => array_map(function($i){ return $i->to_array(); }, \Group::all()),
        ]);
    }

    /**
     * View user profile
     *
     * @param Request $request
     * @param $matches
     * @return mixed|string
     */
    public function profile(Request $request, $matches)
    {
        try {
            /** @var \User $user */
            $user = \User::find($matches['id']);
        } catch ( \Exception $e ) {
            return $this->error404($request);
        }

        // User access log filter
        $access_filter = [
            'conditions'    => ['user_id = ?', $user->id]
        ];

        // Paginator access log
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Visit::count($access_filter)]);
        $access_filter['order'] = 'id DESC';
        $access_filter = array_merge($access_filter, $paginator->limit());

        // Unban user
        if ( $request->get('unban') ) {
            $user->ban_time = null;
            $user->ban_user_id = null;
            $user->ban_reason = null;
            $user->save();

            static::redirect_response(
                $this->map->reverse('users.profile', ['id' => $user->id])
            );
        }

        if ( $request->isMethod('post') ) {
            $changed = false;

            // Edit rating
            $rating = intval($request->get('rating', 0));
            if ( $user->rating != $rating ) {
                $user->rating = $rating;
                $changed = true;
            }

            // Change ban user
            $ban_time = $request->get('ban_time', false);
            $ban_reason = $request->get('ban_reason', false);
            if ( $ban_time ) {
                if ( $ban_time == '-1' || strtolower(trim($ban_time)) == 'forever' ) {
                    $ban_time = -1;
                } else {
                    $ban_time = strtotime($ban_time, time());
                }

                $user->ban($this->user, $ban_time, $ban_reason);
                $changed = true;
            }

            // Edit username
            $new_login = $request->get('username');
            if ( $new_login && $new_login != $user->username ) {
                $exists = \User::find_by_username($new_login);
                if ( $exists && $exists->id ) {
                    return static::json_response([
                        'status'    => $this->lang->translate('user.edit.exists', $new_login),
                        'class'     => 'error'
                    ]);
                } else {
                    $changed = true;
                    $user->username = $new_login;
                }
            }

            // Edit email
            $new_email = $request->get('email');
            if ( $new_email && $new_email != $user->email ) {
                $exists = \User::find_by_email($new_email);
                if ( $exists && $exists->id ) {
                    return static::json_response([
                        'status'    => $this->lang->translate('user.edit.exists_email', $new_email),
                        'class'     => 'error'
                    ]);
                } else {
                    $changed = true;
                    $user->email = $new_email;
                }
            }

            // Edit group
            $new_group = intval($request->get('group', $user->group_id));
            if ( !\Group::find($new_group) ) {
                return static::json_response([
                    'status'    => $this->lang->translate('user.edit.wrong_group'),
                    'class'     => 'error'
                ]);
            } else {
                $changed = true;
                $user->group_id = $new_group;
            }

            // Change password
            $new_password = $request->get('new_password');
            if ( $new_password ) {
                $user->password = $new_password;
                if ( strlen($new_password) > 5 && $user->save() ) {
                    return static::json_response([
                        'status'    => $this->lang->translate('form.saved'),
                        'class'     => 'success'
                    ]);
                } else {
                    return static::json_response([
                        'status'    => $this->lang->translate('form.failed'),
                        'class'     => 'error'
                    ]);
                }
            }

            if ( $changed && $user->save() ) {
                return static::json_response([
                    'status'    => $this->lang->translate('form.saved'),
                    'class'     => 'success'
                ]);
            } else {
                return static::json_response([
                    'status'    => $this->lang->translate('form.failed'),
                    'class'     => 'error'
                ]);
            }
        }

        return $this->view->render('users/profile.twig', [
            'title'         => $this->lang->translate('user.profile.name', $user->username),
            'profile'       => $user->to_array(),
            'groups'        => array_map(function($i){ return $i->to_array(); }, \Group::all()),

            // Access log
            'visits_list'   => \Visit::as_array(\Visit::find('all', $access_filter)),
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    /**
     * Create new group
     *
     * @param Request $request
     * @param $matches
     * @return mixed|string
     */
    public function group_create(Request $request, $matches)
    {
        \GroupPermission::updatePermissionsList();
        $perms = \GroupPermission::defaultMap();

        if ( $request->isMethod('post') ) {
            // Saving permissions
            foreach ( $perms as $key => $val ) {
                $new_val = $request->get('perm_'.$key) == 'true';
                $perms[$key] = $new_val;
            }

            $data = [
                'name'  => $request->get('name'),
                'icon'  => $request->get('icon')
            ];

            $group = \Group::create($data);
            if ( $group ) {
                /** @var \Permission $gperms */
                $gperms = $group->getPermissions();
                $gperms->exchangeArray($perms);
                $gperms->save();
                return static::json_response([
                    'status'    => $this->lang->translate('form.saved'),
                    'class'     => 'success'
                ]);
            } else {
                return static::json_response([
                    'status'    => $this->lang->translate('form.failed'),
                    'class'     => 'error'
                ]);
            }
        }

        return $this->view->render('users/group_create.twig', [
            'title'         => $this->lang->translate('user.group.create'),
            'perms'         => $perms,
        ]);
    }

    /**
     * View group profile
     *
     * @param Request $request
     * @param $matches
     * @return mixed|string
     */
    public function group_profile(Request $request, $matches)
    {
        \GroupPermission::updatePermissionsList();
        try {
            /** @var \Group $user */
            $group = \Group::find($matches['id']);
            $perms = $group->getPermissions();
        } catch ( \Exception $e ) {
            $this->error404($request);
            return;
        }

        if ( $request->isMethod('post') ) {
            // Moving users
            $new_group = intval($request->get('new_group', $group->id));
            if ( $new_group != $group->id ) {
                $update = ['group_id' => $new_group];
                \User::table()->update($update, ['group_id' => $group->id]);
            }

            // Saving permissions
            foreach ( $perms as $key => $val ) {
                $new_val = intval($request->get('perm_'.$key));
                $perms[$key] = $new_val;
            }

            // Saving data
            $new_name = $request->get('name');
            $new_icon = $request->get('icon');
            if ( $new_name != $group->name ) {
                $group->name = $new_name;
            }

            if ( $new_icon != $group->icon ) {
                $group->icon = $new_icon;
            }

            if ( $group->save() && $perms->save() ) {
                \GroupPermission::updatePermissionsList();
                return static::json_response([
                    'status'    => $this->lang->translate('form.saved'),
                    'class'     => 'success'
                ]);
            } else {
                return static::json_response([
                    'status'    => $this->lang->translate('form.failed'),
                    'class'     => 'error'
                ]);
            }
        }

        // Filter groups
        $groups = \Group::all();
        $groups = array_map(function($i){ return $i->to_array(); }, $groups);

        return $this->view->render('users/group_profile.twig', [
            'title'         => $this->lang->translate('user.group.name', $group->name),
            'group'         => $group->to_array(),
            'perms'         => $perms,
            'groups'        => $groups
        ]);
    }

    /**
     * View groups
     *
     * @param Request $request
     * @return mixed
     */
    public function groups_list(Request $request)
    {
        $filter = [];
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Group::count()]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter groups
        $groups = \Group::all($filter);
        $groups = array_map(function($i){ return $i->to_array(); }, $groups);
        return $this->view->render('users/groups.twig', [
            'title'         => $this->lang->translate('user.groups'),
            'groups_list'   => $groups,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    /**
     * View users list
     *
     * @param Request $request
     * @param $matches
     * @return mixed
     */
    public function users_list(Request $request, $matches)
    {
        $filter = [];
        $title = $this->lang->translate('user.list');

        // Filtering by user
        if ( $request->get('group') ) {
            $filter['conditions'] = ['group_id = ?', intval($request->get('group'))];
            $group = \Group::find($request->get('group'));
            if ( $group ) {
                $title = $this->lang->translate('user.profile.group') . ' ' . $group->name;
            }
        }

        // Ordering table
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, $filter ? \User::count($filter) : \User::count()]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter users
        $users = \User::all($filter);
        $users = array_map(function($i){ return $i->to_array(); }, $users);
        return $this->view->render('users/list.twig', [
            'title'         => $title,
            'users_list'    => $users,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }
} 