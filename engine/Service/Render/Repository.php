<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Render;


/**
 * Class Repository
 * @package Service\Render
 */
class Repository
{
    const VERSION_LATEST = null;

    /**
     * @var string
     */
    static $api_key = '2663c73014d2e4d6d1778cc8ad9fd010';

    /**
     * @var string
     */
    static $app_id = '2QWLVLXZB6';

    /**
     * @var string
     */
    private $cache_dir;

    /**
     * @param $cache_dir
     */
    public function __construct($cache_dir)
    {
        $this->cache_dir = $cache_dir;
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function find_repository($query)
    {
        $request = array(
            'apiKey'    => static::$api_key,
            'appID'     => static::$app_id,
            'params'    => $this->query_string([
                'query' => $query,
                'hitsPerPage' => 50
            ])
        );

        $ch = \curl_init('https://2qwlvlxzb6-dsn.algolia.net/1/indexes/libraries/query');
        \curl_setopt($ch, CURLOPT_POST, true);
        \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = \curl_exec($ch);
        \curl_close($ch);

        return json_decode($response, true)['hits'];
    }

    /**
     * @param $repository
     * @param null $version
     * @return array|string
     */
    public function get_repository($repository, $version = Repository::VERSION_LATEST)
    {
        # Loading cache
        if ( is_array($cache = $this->get_cache($repository, $version)) ) {
            return $cache;
        }

        # Loading data from server
        $repository_data = reset($this->find_repository($repository));
        if ( !$repository_data ) {
            return '';
        }

        $url = sprintf(
            'https://cdnjs.com/libraries/%s/%s',
            $repository_data['name'], !$version ? $repository_data['version'] : $version
        );
        $main_url = sprintf(
            'https://cdnjs.cloudflare.com/ajax/libs/%s/%s/',
            $repository_data['name'], !$version ? $repository_data['version'] : $version
        );

        $ch = \curl_init($url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = \curl_exec($ch);
        \curl_close($ch);

        preg_match_all("/class='library-url'>(.+?)<\/p>/i", $response, $matches);

        # Clearify cdns
        $cdns = [];
        if ( isset($matches[1]) ) {
            foreach ( $matches[1] as $file ) {
                $file = str_replace('&#x2F;', '/', $file);
                $name = str_replace($main_url, '', $file);
                $cdns[$name] = $file;
            }
        }

        $response_data = [
            'files' => $cdns,
            'info'  => $repository_data
        ];

        # Saving cache
        $this->set_cache($repository, $response_data, $version);
        return $response_data;
    }

    /**
     * @param $repository
     * @param null $version
     * @return bool|mixed
     */
    private function get_cache($repository, $version = Repository::VERSION_LATEST)
    {
        $repository_hash = md5($repository . $version) . '.json';
        $repository_path = $this->cache_dir . S . $repository_hash;
        if ( !file_exists($repository_path) ) {
            return false;
        }

        return json_decode(
            file_get_contents($repository_path),
            true
        );
    }

    /**
     * @param $repository
     * @param null $version
     * @param array $version
     * @return bool|mixed
     */
    private function set_cache($repository, $response, $version = Repository::VERSION_LATEST)
    {
        $repository_hash = md5($repository . $version) . '.json';
        $repository_path = $this->cache_dir . S . $repository_hash;
        return file_put_contents(
            $repository_path,
            json_encode(
                $response,
                JSON_PRETTY_PRINT
            )
        );
    }

    /**
     * @param array $fields
     * @return string
     */
    private function query_string(array $fields)
    {
        $result = [];
        foreach ( $fields as $key => $val ) {
            $result[] = $key . '=' . urlencode($val);
        }

        return implode('&', $result);
    }
}