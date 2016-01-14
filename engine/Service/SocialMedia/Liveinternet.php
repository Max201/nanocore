<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */
namespace Service\SocialMedia;

use System\Engine\NCModuleCore;
use System\Engine\NCService;
use System\Environment\Env;
use System\Environment\Options;


/**
 * Class Liveinternet
 */
class Liveinternet extends NCService
{
    use API;

    const CONFIG = 'SocialMedia.setup';

    /**
     * @var Liveinternet
     */
    static $instance;

    /**
     * @var Options
     */
    private $conf;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $date = 'now';

    /**
     * Create stat instance
     */
    public function __construct()
    {
        $this->conf = $this->config('liveinternet');
        $this->domain = Env::$request->getHttpHost();

        if ( !$this->is_registered() ) {
            $this->register($this->domain);
        }
    }

    /**
     * @return Liveinternet
     */
    public function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Liveinternet();
        }

        return static::$instance;
    }

    /**
     * @param $date
     */
    public function set_date($date)
    {
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function visits()
    {
        return static::to_array(static::request('index'));
    }

    /**
     * @return bool
     */
    public function is_registered()
    {
        return $this->conf['username'] && $this->conf['password'];
    }

    /**
     * @return bool
     */
    public function register()
    {
        $params = [
            'rules' => 'agreed',
            'www' => '',
            'type' => 'site',
            'url' => 'http://' . $this->domain,
            'aliases' => 'http://www.' . $this->domain,
            'name' => 'Test com',
            'email' => 'webmaster@' . $this->domain,
            'keywords' => 'asd',
            'private' => '',
            'language' => 'ru',
            'group' => '',
            'nick' => $this->domain,
            'subscribe' => 'on',
            'confirmed' => ' зарегистрировать &gt;&gt; ',
            'random' => mt_rand(1000000000, 9999999999)
        ];

        $headers = [
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Host'          => 'www.liveinternet.ru',
            'Origin'        => 'http://www.liveinternet.ru',
            'Upgrade-Insecure-Requests' => 1,
            'Cookie'        => 'chbx=guest',
            'User-Agent'    => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/47.0.2526.73 Chrome/47.0.2526.73 Safari/537.36'
        ];

        $password = '123';
        $params['password'] = $password;
        $params['check'] = $password;

        # Receive form
        $response = static::POST('http://www.liveinternet.ru/add', $params, 'http://www.liveinternet.ru/add', $headers);

        if ( static::is_success($response) ) {
            $this->conf['username'] = $this->domain;
            $this->conf['password'] = $params['password'];
            $this->update_config('liveinternet', $this->conf->getArrayCopy());
            return [
                'username'  => $this->domain,
                'password'  => $params['password']
            ];
        } else {
            return false;
        }
    }

    /**
     * @param $response
     * @return string
     */
    private static function is_success($response)
    {
        preg_match('#action=/code#i', $response, $m);
        return boolval($m);
    }

    /**
     * @param $method
     * @param $lang
     * @return string
     */
    private function url_stat($method, $lang)
    {
        $date = $this->date == 'now' ? gmdate('Y-m-d') : $this->date;
        return 'http://www.liveinternet.ru/stat/'
                . $this->domain . '/'
                . $method . '.csv?date=' . $date
                . '&lang=' . $lang;
    }

    /**
     * @param $method
     * @return mixed
     */
    private function request($method)
    {
        $full_lang = NCModuleCore::load_lang()->pack;
        $lang = reset(explode('_', $full_lang));
        $url = $this->url_stat($method, $lang);
        return file_get_contents($url);
    }

    /**
     * @param $response
     * @return array
     */
    private function to_array($response)
    {
        $response = explode("\n", str_replace('"', '', $response));
        $result_array = [];

        $keys_count = count(explode(';', $response[0]));
        for ( $l = 0; $l < count($response); $l++ ) {
            $line = $response[$l];

            // Process keys on first line
            if ( $l == 0 ) {
                $sets = explode(';', substr($line, 1));
                foreach ( $sets as $fset ) {
                    $result_array[$fset] = [];
                }

                continue;
            } else {
                $sets = explode(';', $line);
            }

            // Process data
            $key_to_set = array_shift($sets);
            $next = 0;
            foreach ( $result_array as $k => $v ) {
                if ( !$key_to_set ) continue;

                $v[$key_to_set] = $sets[$next];
                $result_array[$k] = $v;
                $next++;
            }
        }

        return [
            'response' => $result_array,
            'keys' => array_keys(
                $result_array[array_keys($result_array)[0]]
            )
        ];
    }
}
