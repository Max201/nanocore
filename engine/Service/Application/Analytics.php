<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use Symfony\Component\HttpFoundation\Cookie;
use System\Engine\NCService;
use System\Environment\Env;


/**
 * Class Analytics
 * @package Service\Application
 */
class Analytics extends NCService
{
    /**
     * @var Analytics
     */
    static $instance;

    /**
     * @var int
     */
    private $ip;

    /**
     * @var string
     */
    private $ua;

    /**
     * @var int
     */
    private $user_id = null;

    /**
     * @var string
     */
    private $browser;

    /**
     * @var string
     */
    private $browser_version;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var string
     */
    private $referer;

    /**
     * @var string
     */
    private $query;

    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $time_start;

    /**
     * Create new visit
     */
    public function __construct($uid = null)
    {
        $this->ip = static::long_ip();
        $this->ua = static::ua();
        $this->user_id = $uid;

        $browser_data = static::browser($this->ua);
        $this->browser = $browser_data['name'];
        $this->browser_version = $browser_data['version'];
        $this->platform = $browser_data['platform'];

        $this->referer = static::referer();
        $this->query = static::search_query($this->referer);

        $this->time = time();
        $this->time_start = static::start_session();
    }

    /**
     * Saving visit
     */
    public function save()
    {
        $visit = new \Visit($this->to_array());
        return $visit->save();
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return [
            'ip'        => $this->ip,
            'ua'        => $this->ua,
            'user_id'   => $this->user_id,
            'page'      => Env::$request->server->get('REQUEST_URI', '/'),
            'referer'   => $this->referer,
            'internal'  => $this->internal($this->referer),
            'domain'    => $this->domain($this->referer),
            'search'    => $this->query,
            'browser'   => $this->browser,
            'version'   => $this->browser_version,
            'platform'  => $this->platform,
            'time'      => $this->time,
            'time_start'=> $this->time_start
        ];
    }

    /**
     * @param string $time_shift
     * @return Analytics
     */
    static function instance($uid = null)
    {
        if ( !static::$instance ) {
            static::$instance = new Analytics($uid);
        }

        return static::$instance;
    }

    /**
     * @param $referer
     * @return null|string
     */
    static function search_query($referer)
    {
        if ( !$referer || static::internal($referer) ) {
            return null;
        }

        // Parse query string
        $qs = explode('?', $referer, 2);
        if ( count($qs) < 2 ) {
            return null;
        }

        $qs = end($qs);
        parse_str($qs, $get);

        // Get query terms
        $search_keys = [
            'q',
            'query',
            'search',
            'text'
        ];

        $query_term = null;
        foreach ( $search_keys as $key ) {
            if ( array_key_exists($key, $get) ) {
                $query_term = $get[$key];
            }
        }

        return $query_term;
    }

    /**
     * @param $link
     * @return bool
     */
    static function internal($link)
    {
        return strpos($link, Env::$request->getHttpHost()) > -1;
    }

    /**
     * @return string
     */
    static function ua()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * @return string
     */
    static function referer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

    /**
     * @return string
     */
    static function ip()
    {
        if ( !empty($_SERVER['REMOTE_ADDR']) ) {
            return $_SERVER['REMOTE_ADDR'];
        } elseif ( !empty($_SERVER['HTTP_CLIENT_IP']) ) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    /**
     * @param $url
     * @return null
     */
    static function domain($url)
    {
        $url = parse_url($url);
        return isset($url['host']) ? $url['host'] : null;
    }

    /**
     * @param null $ip
     * @return int
     */
    static function long_ip($ip = null)
    {
        if ( is_null($ip) ) {
            $ip = static::ip();
        }

        return ip2long($ip);
    }

    /**
     * @return mixed
     */
    static function start_session()
    {
        $start = Env::$request->cookies->get('_ss', time());
        Env::$response->headers->setCookie(new Cookie('_ss', $start));
        return $start;
    }

    /**
     * @param $u_agent
     * @return array
     */
    static function browser($u_agent)
    {
        $bname = 'bot';
        $platform = 'unknown';
        $version= 'unknown';

        // Detect platform
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        } else {
            $platform = 'unknown';
        }

        // Detect browser
        if( preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent) ) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i',$u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i',$u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i',$u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (preg_match('/Twitterbot/i',$u_agent)) {
            $bname = 'Twitter';
            $ub = "Twitterbot";
        } elseif (preg_match('/Googlebot/i',$u_agent)) {
            $bname = 'Google';
            $ub = "Googlebot";
        } elseif (preg_match('/Yahoo/i',$u_agent)) {
            $bname = 'Yahoo';
            $ub = "Yahoo! Slurp";
        } elseif (preg_match('/YandexBot/i',$u_agent)) {
            $bname = 'Yandex';
            $ub = "YandexBot";
        } elseif (preg_match('/vkShare/i',$u_agent)) {
            $bname = 'VKontakte';
            $ub = "VKShare";
        } elseif (preg_match('/OpenHoseBot/i',$u_agent)) {
            $bname = 'OpenHose';
            $ub = "OpenHoseBot";
        } elseif (preg_match('/SemrushBot/i',$u_agent)) {
            $bname = 'Semrush';
            $ub = "SemrushBot";
        } elseif (preg_match('/TelegramBot/i',$u_agent)) {
            $bname = 'Telegram';
            $ub = "TelegramBot";
        } elseif (preg_match('/Google-HTTP/i',$u_agent)) {
            $bname = 'Google';
            $ub = "Google-HTTP-Java-Client";
        } else {
            $bname = 'bot';
            $ub = 'bot';
        }

        // Parse version
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        preg_match_all($pattern, $u_agent, $matches);
        if ( count($matches['browser']) != 1 ) {
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)) {
                $version= $matches['version'][0];
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }

        if ( $version==null || $version=="" ) {
            $version="?";
        }

        return array(
            'name'          => $bname,
            'version'       => $version,
            'platform'      => $platform,
        );
    }
} 