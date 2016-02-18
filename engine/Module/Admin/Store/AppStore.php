<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin\Store;


use Symfony\Component\HttpFoundation\Request;

/**
 * Class AppStore
 * @package Module\Admin\Store
 */
trait AppStore
{
    /**
     * Application server
     *
     * @var string
     */
    static $server = 'http://core.nanolab.pw/store/';

    /*
     * App store list
     */
    public function store_list(Request $request, $matches)
    {

    }

    /**
     * @param string $method
     * @param array $data
     * @return array
     */
    private static function request($method, $data = [])
    {
        $data['key'] = '';
        $request = [];
        foreach ( $data as $key => $value ) {
            $request[] = $key . '=' . urlencode($value);
        }

        return json_decode(
            file_get_contents(static::$server . $method . '?' . implode('&', $request)),
            true
        );
    }
} 