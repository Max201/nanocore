<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class User
 * @package Model
 */
class User extends Model
{
    static $before_create = array('set_session', 'register_date');
    static $before_save = array('set_password');

    /**
     * @var array
     */
    static $belongs_to = array(
        ['group', 'class_name' => 'Group', 'foreign_key' => 'group_id'],
        ['ban_user', 'class_name' => 'User', 'foreign_key' => 'ban_user_id'],
    );

    /**
     * @param int $size
     * @param string $default
     * @return string
     */
    public function gravatar($size = 128, $default = null)
    {
        return static::get_gravatar_url($this->email, $size, $default);
    }

    /**
     * @param $email
     * @param int $size
     * @param null $default
     * @return string
     */
    static function get_gravatar_url($email, $size = 128, $default = null)
    {
        if ( $size > 2048 ) {
            $size = 2048;
        }

        if ( $size < 8 ) {
            $size = 8;
        }

        $hash = md5(strtolower(trim($email)));

        return 'https://www.gravatar.com/avatar/' . $hash
            . '?s=' . $size
            . ($default ? '&d=' . urlencode($default) : '');
    }

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

        if ( strlen($password) < 32 ) {
            $this->assign_attribute('password', static::encrypt($password));
        }
    }

    /**
     * Generates random session for new user
     */
    public function set_session()
    {
        $this->assign_attribute('session_id', static::encrypt($this->username . microtime(true) . $this->password));
    }

    /**
     * Defines register date
     */
    public function register_date()
    {
        $this->assign_attribute('register_date', time());
    }

    /**
     * Ban user
     *
     * @param User $from
     * @param int  $time
     * @param null $reason
     */
    public function ban(User $from, $time = -1, $reason = null)
    {
        $this->assign_attribute('ban_time', $time);
        $this->assign_attribute('ban_user_id', $from->id);
        $this->assign_attribute('ban_reason', $reason);
        $this->save();
    }

    /**
     * @return bool
     */
    public function banned()
    {
        return $this->ban_time > time() || $this->ban_time == -1;
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
        return $user->to_array();
    }

    /**
     * @return array|null
     */
    public function to_array()
    {
        if ( !$this->id ) {
            return null;
        }

        $user = parent::to_array();
        unset($user['session_id'], $user['password']);

        return array_merge(
            array(
                'banned'        => $this->banned(),
                'ban_user'      => $this->ban_user ? $this->ban_user->to_array() : [],
                'group'         => $this->group->to_array(),
                'permissions'   => $this->getPermissions()->getArrayCopy()
            ),
            $user
        );
    }
}