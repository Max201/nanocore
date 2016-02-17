<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


class IPWall 
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array|null
     */
    private $banned;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->app = $application;
        $this->banned = $this->app->conf->get('banned_ip');
    }

    /**
     * @return array|null
     */
    public function banned()
    {
        return $this->banned;
    }

    /**
     * @param $ip
     */
    public function add($ip)
    {
        if ( !in_array($ip, $this->banned) ) {
            $this->banned[] = $ip;
        }
    }

    /**
     * @param $ip
     */
    public function del($ip)
    {
        if ( in_array($ip, $this->banned) ) {
            $ips = [];
            foreach ( $this->banned as $banned ) {
                if ( $banned != $ip ) $ips[] = $banned;
            }

            $this->banned = $ips;
        }
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->app->conf->set('banned_ip', $this->banned);
        return $this->app->conf->save();
    }

    /**
     * @param $ip
     * @return bool
     */
    public function allowed($ip)
    {
        return !in_array($ip, $this->app->conf->get('banned_ip'));
    }
} 