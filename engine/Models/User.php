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
    static $before_create = array('set_session', 'set_password');

    /**
     * Encrypt password
     * @param $password
     */
    public function set_password($password = null)
    {
        if ( is_null($password) ) {
            $password = $this->password;
        }

        $this->assign_attribute('password', md5(substr(md5($password), 4, -4)));
    }

    /**
     * Generates random session for new user
     */
    public function set_session()
    {
        $this->assign_attribute('session', md5(microtime(true)));
    }
}