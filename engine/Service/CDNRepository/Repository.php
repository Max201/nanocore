<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\CDNRepository;


/**
 * Class Repository
 * @package Service\CDNRepository
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

        $ch = curl_init('https://2qwlvlxzb6-dsn.algolia.net/1/indexes/libraries/query');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true)['hits'];
    }

    /**
     * @param $repository
     * @param null $version
     * @return array|string
     */
    public function get_reposiroty($repository, $version = Repository::VERSION_LATEST)
    {
        $repository = reset($this->find_repository($repository));
        if ( !$repository ) {
            return '';
        }

        $url = sprintf(
            'https://cdnjs.com/libraries/%s/%s',
            $repository['name'], !$version ? $repository['version'] : $version
        );
        $main_url = sprintf(
            'https://cdnjs.cloudflare.com/ajax/libs/%s/%s/',
            $repository['name'], !$version ? $repository['version'] : $version
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

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

        return [
            'files' => $cdns,
            'info'  => $repository
        ];
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