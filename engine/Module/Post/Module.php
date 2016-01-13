<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Post;


use Service\Application\Translate;
use Service\Module\RecursiveTree;
use Service\Paginator\Listing;
use Service\Render\Theme;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCService;
use System\Engine\NCSitemapBuilder;
use System\Environment\Env;
use System\Environment\Options;
use System\Util\Calendar;
use User;


/**
 * Class Module
 * @package Module\Post
 */
class Module extends NCModule
{
    const SITEMAP = true;

    public function route()
    {
        $this->map->addRoute('/', [$this, 'news'], 'news');
        $this->map->addPattern('<id:\d+>-<slug:.+>.html', [$this, 'post'], 'post');
        $this->map->addPattern('category/<id:\d+>', [$this, 'category'], 'category');
    }

    /**
     * @param NCSitemapBuilder $builder
     * @return NCSitemapBuilder
     */
    public function sitemap(NCSitemapBuilder $builder)
    {
        // News page
        $builder->add_url(
            $this->map->reverse('news'),
            1,
            'now',
            'daily'
        );

        // Posts map
        $posts = \Post::all();
        foreach ($posts as $entry) {
            $builder->add_url(
                $this->map->reverse('post', [$entry->id, $entry->slug]),
                0.9,
                $entry->updated_at,
                'weekly'
            );
        }

        // Categories
        $categories = \PostCategory::all();
        foreach ($categories as $category) {
            $builder->add_url(
                $this->map->reverse('category', [$category->id]),
                0.9,
                mktime(0, 0, 0),
                'daily'
            );
        }

        return $builder;
    }

    /**
     * @param NCModule $module
     * @param Theme $view
     * @param Translate $lang
     * @return array
     */
    public static function globalize(NCModule $module, Theme $view, Translate $lang)
    {
        $categories = \PostCategory::listing();
        /** @var RecursiveTree $recursive_tree */
        $recursive_tree = NCService::load('Module.RecursiveTree', [$categories]);
        $categories = array_map(function($item) use($recursive_tree) {
            $result = $item->to_array();
            $result['link'] = '/post/category/' . $item->id;
            $result['posts'] = \Post::count([
                'conditions'    => ['category_id IN (?)', $recursive_tree->childs($item->id)]
            ]);

            return $result;
        }, $categories);

        return [
            'post_categories'   => $categories
        ];
    }

    /**
     * @param Request $request
     * @param Options $matches
     * @return string
     */
    public function category(Request $request, Options $matches = null)
    {
        /** @var \PostCategory $category */
        $category = \PostCategory::find($matches->get('id'));
        if ( $category ) {
            /** @var RecursiveTree $recursive_tree */
            $recursive_tree = NCService::load('Module.RecursiveTree', [\PostCategory::listing()]);

            // Filter conditions
            $filter = [
                'conditions'    => ['category_id IN (?)', $recursive_tree->childs($category->id)]
            ];

            // Rows count
            $rows = \Post::count($filter);

            // Paginator
            /** @var Listing $pagination */
            $pagination = NCService::load('Paginator.Listing', [$request->page, $rows]);

            // Limitation
            $filter = array_merge($filter, $pagination->limit());

            // Ordering
            if ( $request->order ) {
                $filter['order'] = $request->order;
            } else {
                $filter['order'] = 'updated_at DESC';
            }

            // Get posts
            $posts = \Post::all($filter);

            // Rendering
            return $this->view->render('posts/list.twig', [
                'title'     => $category->title,
                'category'  => $category->to_array(),
                'posts'     => array_map(function($i){ return $i->to_array(); }, $posts),
                'listing'   => $pagination->pages(),
                'page'      => $request->page
            ]);
        }

        return $this->error404($request);
    }

    /**
     * Post page
     */
    public function post(Request $request, $matches)
    {
        /** @var \Post $post */
        $post = \Post::find_by_id($matches->get('id'));
        if ( $post ) {
            if ( Env::$request->cookies->get('_p_viewed') != $post->id ) {
                $post->views = $post->views + 1;
                $post->save();

                Env::$response->headers->setCookie(new Cookie('_p_viewed', $post->id));
            }

            Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $post->updated_at));
            return $this->view->render('posts/default.twig', [
                'post'  => $post->to_array(),
                'title' => $post->title,
            ]);
        }

        return $this->error404($request);
    }

    /**
     * Post page
     */
    public function news(Request $request, $matches)
    {
        $filter = [];

        // Rows count
        $rows = \Post::count();

        // Paginator
        /** @var Listing $pagination */
        $pagination = NCService::load('Paginator.Listing', [$request->page, $rows]);

        // Limitation
        $filter = array_merge($filter, $pagination->limit());

        // Ordering
        if ( $request->order ) {
            $filter['order'] = $request->order;
        } else {
            $filter['order'] = 'updated_at DESC';
        }

        // Get posts
        $posts = \Post::all($filter);

        // Rendering
        return $this->view->render('posts/list.twig', [
            'title'     => $this->lang->translate('post.title'),
            'posts'     => array_map(function($i){ return $i->to_array(); }, $posts),
            'listing'   => $pagination->pages(),
            'page'      => $request->page
        ]);
    }
} 