<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Page;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCSitemapBuilder;
use System\Environment\Env;
use System\Util\Calendar;
use User;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    const SITEMAP = true;

    public function route()
    {
        $this->map->addPattern('<id:\d+>-<slug:.+>.html', [$this, 'page'], 'page');
    }

    public function sitemap(NCSitemapBuilder $builder)
    {
        $pages = \Page::all();
        foreach ( $pages as $page ) {
            $builder->add_url(
                $this->map->reverse('page', [$page->id, $page->slug]),
                0.9,
                $page->updated_at,
                'monthly'
            );
        }

        return $builder;
    }

    static function globalize($module, $theme, $translate)
    {
        /*
         * Add all pages to context
         */
        $pages = array_map(function($p) {
            $page = $p->to_array();
            $page['url'] = '/page/' . $page['id'] . '-' . $page['slug'] . '.html';
            return $page;
        }, \Page::all());

        /*
         * Short description filter
         */
        $theme->twig->addFilter(new \Twig_SimpleFilter('short', function($value){
            $short_tag = '<br id="short"/>';
            if ( strpos($value, $short_tag) > 0 ) {
                return reset(explode($short_tag, $value, 2));
            }

            return $value;
        }));

        return [
            'static_pages'  => $pages
        ];
    }

    /**
     * User registration page
     */
    public function page(Request $request, $matches)
    {
        $page = \Page::find_by_id($matches->get('id'));
        if ( !$page ) {
            return $this->error404($request);
        }

        Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $page->updated_at));
        return $this->view->render('pages/' . $page->template, [
            'page'  => $page->to_array(),
            'title' => $page->title,
            'author'=> $page->author->to_array()
        ]);
    }
} 