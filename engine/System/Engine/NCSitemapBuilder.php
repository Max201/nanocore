<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\Env;
use System\Environment\Options;


/**
 * Class NCSitemapBuilder
 * @package System\Engine
 */
class NCSitemapBuilder
{
    const TYPE_URLSET = 1;
    const TYPE_SITEMAP_INDEX = 2;

    /**
     * @var string
     */
    private $xml = '<?xml version="1.0" encoding="UTF-8"?>';

    /**
     * @var array
     */
    private $urls = [];

    /**
     * @var int
     */
    private $type;

    /**
     * @param array $urls
     * @param int $type
     */
    public function __construct($urls = [], $type = self::TYPE_URLSET)
    {
        $this->urls = $urls;
        $this->type = $type;
    }

    /**
     * @param $url
     * @param $priority
     * @param $last_modified
     * @param $change_freq
     */
    public function add_url($url, $priority = 0.9, $last_modified = 'now', $change_freq = 'daily')
    {
        if ( strtolower($last_modified) == 'now' ) {
            $last_modified = time();
        }

        $this->urls[] = new Options([
            'loc'       => $url,
            'lastmod'   => date('c', $last_modified),
            'changefreq'=> $change_freq,
            'priority'  => $priority
        ]);
    }

    /**
     * @param $url
     * @param string $last_modified
     */
    public function add_sitemap($url, $last_modified = 'now')
    {
        if ( strtolower($last_modified) == 'now' ) {
            $last_modified = time();
        }

        $this->urls[] = new Options([
            'loc'       => $url,
            'lastmod'   => date('c', $last_modified)
        ]);
    }

    /**
     * @param $url
     * @return Options
     */
    private function _filter_url($url)
    {
        if ( !($url instanceof Options) ) {
            $url = new Options(['loc' => $url]);
        }

        $scheme = Env::$request->isSecure() ? 'https://' : 'http://';
        $domain = Env::$request->getHttpHost();
        $loc = $url->get('loc');
        $loc_pairs = explode('?', $loc);
        if ( isset($loc_pairs[1]) ) {
            $loc_pairs[1] = rawurlencode($loc_pairs[1]);
        }

        $url['loc'] = $scheme . $domain . implode($loc_pairs, '?');
        return $url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $xml = $this->xml;
        switch ($this->type) {
            case self::TYPE_URLSET:
                $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                foreach ( $this->urls as $url ) {
                    $url = $this->_filter_url($url);

                    $node = '<url>';
                    $node .= '<loc>' . $url->get('loc') . '</loc>';
                    $node .= '<lastmod>' . $url->get('lastmod', date('c')) . '</lastmod>';
                    $node .= '<changefreq>' . $url->get('changefreq', 'never') . '</changefreq>';
                    $node .= '<priority>' . $url->get('priority', 0.8) . '</priority>';
                    $node .= '</url>';
                    $xml .= $node;
                }

                $xml .= '</urlset>';

                break;

            default:
                $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                foreach ( $this->urls as $url ) {
                    $url = $this->_filter_url($url);

                    $node = '<sitemap>';
                    $node .= '<loc>' . $url->get('loc') . '</loc>';
                    $node .= '<lastmod>' . $url->get('lastmod', date('c')) . '</lastmod>';
                    $node .= '</sitemap>';
                    $xml .= $node;
                }
                $xml .= '</sitemapindex>';
        }

        return $xml;
    }
} 