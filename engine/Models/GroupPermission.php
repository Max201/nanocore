<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */
use System\Engine\NCService;
use System\Environment\Options;
use ActiveRecord\Model;


/**
 * Class GroupPermission
 * @package Entity
 */
class GroupPermission extends Model
{
    /**
     * @var Options
     */
    private static $permissionsMap;

    /**
     * @param bool $validate
     * @return bool
     */
    public function save($validate = true)
    {
        if ( parent::save($validate) ) {
            return static::updatePermissionsList();
        }

        return false;
    }

    /**
     * @param string $name
     * @param bool $default
     * @param string $title
     * @return Model
     */
    static function create($name, $default = false, $title = null)
    {
        $gp = new static([
            'permission'    => $name,
            'default'       => $default ? '1' : '0'
        ]);


        if ( is_null($title) || is_string($title) ) {
            $title = is_null($title) ? ucfirst(str_replace('.', ' ', $title)) : $title;
            $description = glob(ROOT . S . 'engine' . S . 'Language' . S . '*' . S . 'group.json');
            foreach ( $description as $fd ) {
                $content = json_decode(file_get_contents($fd), true);
                $content[$name] = $title;

                @file_put_contents($fd, json_encode($content, JSON_PRETTY_PRINT));
            }
        } elseif ( is_array($title) ) {
            foreach ( $title as $lng => $t ) {
                $path = ROOT . S . 'engine' . S . 'Language' . S . $lng;
                if ( !is_dir($path) ) {
                    @mkdir($path, 0777, true);
                }

                $data = [$name => $t];
                @file_put_contents($path . S . 'group.json', json_encode($data, JSON_PRETTY_PRINT));
            }
        }

        return $gp->save();
    }

    /**
     * @param Group $group
     * @return Permission
     */
    public static function getByGroup(Group $group)
    {
        return new Permission(
            $group,
            static::getPermissionsMap()->map->get(
                $group->id, static::defaultMap()
            )
        );
    }

    /**
     * @param User $user
     * @return Permission
     */
    public static function getByUser(User $user)
    {
        return new Permission(
            $user->group,
            static::getPermissionsMap()->map->get($user->group->id)
        );
    }

    /**
     * @return bool
     */
    public static function updatePermissionsList()
    {
        // Build permissions list
        $mods = static::find('all');
        $modsList = [];
        foreach ( $mods as $mod ) {
            $modsList[$mod->permission] = boolval(intval($mod->default));
        }

        // Get permissions map
        $permMap = static::getPermissionsMap();

        // Update json-map for all groups
        $groups = Group::find('all');
        foreach ( $groups as $group ) {
            $permMap->map[$group->id] = static::mergePermissions($modsList, $permMap->map->get($group->id, []));
            $permMap->save();
        }

        return $permMap->save();
    }

    /**
     * @return \Service\User\Permissions
     */
    public static function getPermissionsMap()
    {
        if ( !static::$permissionsMap ) {
            static::$permissionsMap = NCService::load('User.Permissions');
        }

        return static::$permissionsMap;
    }

    /**
     * @return array
     */
    public static function defaultMap()
    {
        $perms = GroupPermission::all();
        $map = [];

        foreach ( $perms as $rule ) {
            $map[$rule->permission] = boolval(intval($rule->default));
        }

        return $map;
    }

    /**
     * @param $mods
     * @param $current
     * @return array
     */
    private static function mergePermissions($mods, $current)
    {
        $newPerms = [];
        foreach ( $mods as $key => $val ) {
            if ( array_key_exists($key, $current) ) {
                $newPerms[$key] = $current[$key];
                continue;
            }

            $newPerms[$key] = $val;
        }

        return $newPerms;
    }
} 