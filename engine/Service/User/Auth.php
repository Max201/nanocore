<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 22:49
 */

namespace Service\User;


use ActiveRecord\DateTime;
use Symfony\Component\HttpFoundation\Cookie;
use System\Environment\Env;
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
        $user = User::find_by_session_id($session);
        if ( $user && $user->id ) {
            $user->last_visit = time();
            $user->save();
        }

        return $user;
    }

    /**
     * @param $username
     * @param $password
     * @return User
     */
    public function authenticate($username, $password)
    {
        $password = User::encrypt($password);
        return User::find_by_username_and_password($username, $password);
    }

    /**
     * @param User $user
     * @param string $expiry
     * @return bool
     */
    public function login(User $user, $expiry = '+50 weeks')
    {
        $session = User::encrypt($user->session_id . microtime(true));
        Env::$response->headers->setCookie(new Cookie('sess', $session, new DateTime($expiry), '/'));
        $user->session_id = $session;
        $user->save();
    }
}