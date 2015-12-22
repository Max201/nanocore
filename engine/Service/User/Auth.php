<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 22:49
 */

namespace Service\User;


use User;
use System\Engine\NCService;


/**
 * Class Auth
 * @package Service\User
 */
class Auth extends NCService
{
    /**
     * @var Auth
     */
    static $instance;

    /**
     * @param string $session
     * @return User
     */
    public function identify($session)
    {

    }

    /**
     * @param $username
     * @param $password
     * @return User
     */
    public function authenticate($username, $password)
    {

    }

    /**
     * @param User $user
     * @return bool
     */
    public function login(User $user)
    {

    }
}