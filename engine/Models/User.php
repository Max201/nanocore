<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;
use System\Environment\Env;
use Symfony\Component\HttpFoundation\Cookie;


/**
 * Class User
 * @package Model
 */
class User extends Model
{
    static $before_create = array('set_session', 'set_password');

    /**
     * @var array
     */
    static $belongs_to = array(
        array('group', 'class_name' => 'Group')
    );

    /**
     * Encrypt password
     *
     * @param string $src
     * @return string
     */
    static function encrypt($src = '')
    {
        return md5(substr(md5($src), 4, -4));
    }

    /**
     * Encrypt password
     * @param $password
     */
    public function set_password($password = null)
    {
        if ( is_null($password) ) {
            $password = $this->password;
        }

        $this->assign_attribute('password', static::encrypt($password));
    }

    /**
     * Generates random session for new user
     */
    public function set_session()
    {
        $this->assign_attribute('session', md5(microtime(true)));
    }

    /**
     * @return Permission
     */
    public function getPermissions()
    {
        return GroupPermission::getByGroup($this->group);
    }
    /**
     * @param $permission
     * @return bool
     */
    public function can($permission)
    {
        return (bool)$this->getPermissions()[$permission];
    }

    /**
     * @param int $uid
     * @return array
     */
    public static function getAsArray($uid)
    {
        $user = static::find(intval($uid));
        return $user->asArrayFull();
    }

    /**
     * @return array|null
     */
    public function asArrayFull()
    {
        if ( !$this->id ) {
            return null;
        }

        return array_merge(
            array(
                'group' => $this->group->to_array(),
                'permissions'   => $this->getPermissions()->getArrayCopy()
            ),
            $this->to_array()
        );
    }
}