<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Engine\NCService;
use System\Environment\Env;
use System\Environment\Options;


/**
 * Class Vkontakte
 * @package Service\SocialMedia
 */
class Vkontakte extends NCService
{
    use API;

    /**
     * Social settings
     */
    const CONFIG = 'SocialMedia.setup';

    /**
     * VK API Version
     */
    const API_V = '5.42';

    /**
     * @var Vkontakte
     */
    static $instance;

    /**
     * @var Options
     */
    public $conf;

    /**
     * Initialize service
     */
    public function __construct()
    {
        $this->conf = $this->config('vkontakte');

        if ( !$this->conf->get('groups') ) {
            $this->conf['groups'] = [];
        }
    }

    /**
     * @return Vkontakte
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Vkontakte();
        }

        return static::$instance;
    }

    /**
     * @param array $config
     * @return bool
     */
    public function setup($config)
    {
        $this->conf['id'] = $config['id'];
        $this->conf['secret'] = $config['secret'];

        return $this->update_config('vkontakte', $this->conf->getArrayCopy());
    }

    /**
     * @param $code
     * @param $redirect_url
     * @return bool|mixed|null
     */
    public function get_token($code, $redirect_url)
    {
        $params = [
            'client_id'     => $this->conf['id'],
            'client_secret' => $this->conf['secret'],
            'code'          => $code,
            'redirect_uri'  => static::build_url($redirect_url)
        ];

        $request_url = 'https://oauth.vk.com/access_token?' . static::build_request($params);
        $response = json_decode(file_get_contents($request_url), true);
        $response = new Options($response);
        if ( $response->get('access_token') && $response->get('user_id') ) {
            return $this->access_token(
                $response->get('access_token'),
                $response->get('user_id'),
                $response->get('expires_in')
            );
        }

        return false;
    }

    /**
     * @param null $token
     * @param null $user_id
     * @param null $expires
     * @return mixed|null
     */
    public function access_token($token = null, $user_id = null, $expires = null)
    {
        if ( is_null($token) ) {
            return $this->conf->get('token');
        }

        $this->conf['token'] = $token;
        $this->conf['user_id'] = $user_id;
        $this->conf['expires'] = $expires > 0 ? time() + $expires : 0;
        return $this->update_config('vkontakte', $this->conf->getArrayCopy());
    }

    /**
     * @return bool
     */
    public function active()
    {
        $valid = $this->conf->get('expires') > time() || $this->conf->get('expires') == 0;
        return $this->conf->get('token') && $valid;
    }

    /**
     * @return bool
     */
    public function configured()
    {
        return $this->conf->get('id') && $this->conf->get('secret');
    }

    /**
     * @param $redirect
     * @param array $scopes
     * @return string
     */
    public function authorize_url($redirect, $scopes = ['groups'])
    {
        $redirect = static::build_url($redirect);
        $scopes[] = 'offline';
        $scopes[] = 'wall';
        return 'https://oauth.vk.com/authorize?' . static::build_request([
            'client_id'     => $this->conf['id'],
            'display'       => 'page',
            'redirect_uri'  => $redirect,
            'scope'         => implode(',', $scopes),
            'response_type' => 'code',
            'v'             => static::API_V
        ]);
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function request($method, $params = [])
    {
        $params['access_token'] = $this->conf->get('token');
        $request_url = 'https://api.vk.com/method/' . $method . '?' . static::build_request($params);
        return json_decode(file_get_contents($request_url), true);
    }

    /**
     * @return array
     */
    public function m_groups()
    {
        return $this->request('groups.get', [
            'user_id'       => $this->conf->get('user_id'),
            'filter'        => 'admin',
            'extended'      => 1
        ]);
    }

    /**
     * @param int $wall_id
     * @param string $message
     * @param array $params
     * @return int
     */
    public function m_post($wall_id, $message, $params = [])
    {
        $params['owner_id'] = intval($wall_id);
        $params['message'] = strip_tags($message);
        return $this->request('wall.post', $params);
    }
} 