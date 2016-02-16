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
        $this->map->addRoute('new', [$this, 'create'], 'new');

        $this->map->addPattern('<id:\d+>-<slug:.+>.html', [$this, 'post'], 'post');
        $this->map->addPattern('category/<id:\d+>', [$this, 'category'], 'category');
        $this->map->addPattern('list/<id:.+>', [$this, 'user'], 'user');
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
        $posts = \Post::all(['conditions' => ['moderate = ?', 0]]);
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
        // Categories listing
        $categories = function () {
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

            return $categories;
        };

        // User publications
        $publications = function () use($module) {
            $publications = 0;
            if ( $module->user ) {
                $publications = \Post::count(['conditions' => ['author_id = ?', $module->user->id]]);
            }

            return $publications;
        };

        return [
            '_publ'  => lazy_arr('publications', [
                '$categories'   => $categories,
                '$users'        => $publications
            ])
        ];
    }

    /**
     * @param \Post $post
     * @param Theme $view
     * @return bool
     */
    static function export(\Post $post, Theme $view)
    {
        $post->save();
        if ( $post->post_vkontakte && $post->post_twitter ) {
            return true;
        }

        $context = [
            'title' => $post->title,
            'content' => $post->content_plain(),
            'tags' => implode(' ', $post->hashtags()),
            'url' => Env::$request->getSchemeAndHttpHost() . '/post/' . $post->id . '-' . $post->slug . '.html',
            'author' => $post->author->username,
            'category' => $post->category->name,
        ];

        return $post->export([
            'vkontakte'     => $view->render('@assets/templates/smp.vkontakte.twig', $context),
            'twitter'       => $view->render('@assets/templates/smp.twitter.twig', $context),
        ]);
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
                'conditions'    => ['category_id IN (?) AND moderate = ?', $recursive_tree->childs($category->id), 0]
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
                $filter['order'] = 'created_at DESC';
            }

            // Get posts
            $posts = \Post::all($filter);

            // Rendering
            return $this->view->render('posts/list.twig', [
                'title'     => $category->title,
                'category'  => $category->to_array(),
                'posts'     => \Post::as_array($posts),
                'listing'   => $pagination->pages(),
                'page'      => $request->page
            ]);
        }

        return $this->error404($request);
    }

    /**
     * @param Request $request
     * @param Options $matches
     * @return string
     */
    public function user(Request $request, Options $matches = null)
    {
        $who = $matches->get('id');

        if ( $who == 'my' ) {
            $user = $this->user;
        } else {
            /** @var \PostCategory $category */
            $user = \User::find($matches->get('id'));
        }


        if ( $user ) {
            // Filter conditions
            $filter = [
                'conditions'    => ['author_id = ?', $user->id]
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
                $filter['order'] = 'created_at DESC';
            }

            // Get posts
            $posts = \Post::all($filter);

            // Rendering
            return $this->view->render('posts/list.twig', [
                'title'     => $this->lang->translate('post.by_author', $user->username),
                'posts'     => \Post::as_array($posts),
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
                $post->save(true, false);

                Env::$response->headers->setCookie(new Cookie('_p_viewed', $post->id));
            }

            Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $post->updated_at));

            // Same posts
            $same = \Post::find([
                'conditions'    => ['id <> ? AND category_id = ?', $post->id, $post->category_id],
                'offset'        => 0,
                'limit'         => 5
            ]);

            return $this->view->render('posts/default.twig', [
                'post'  => $post->to_array(),
                'title' => $post->title,
                'same'  => \Post::as_array($same)
            ]);
        }

        return $this->error404($request);
    }

    /**
     * Post page
     */
    public function news(Request $request, $matches)
    {
        $filter = [
            'conditions'    => ['moderate = ?', 0]
        ];

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
            $filter['order'] = 'created_at DESC';
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

    /**
     * @param Request $request
     * @param null $matches
     * @return mixed|string
     */
    public function create(Request $request, $matches = null)
    {
        $this->authenticated_only();
        if ( !$this->user->can('publicate') ) {
            return $this->error403($request);
        }

        $title = $this->lang->translate('post.create');

        // Get post for updating
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $post = \Post::find(['conditions' => ['id = ? AND author_id = ?', $id, $this->user->id]]);
            $title = $this->lang->translate('post.editing', $post->title);
        } else {
            $last_category = \PostCategory::last();
            if ( $last_category ) {
                $last_category = $last_category->to_array();
            } else {
                $last_category = null;
            }

            $post = [
                'title'     => $this->lang->translate('post.name'),
                'content'   => '',
                'category'  => $last_category
            ];
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $post instanceof \Post ) {
                $post->title = $request->get('title');
                $post->content = $request->get('content');
                $post->category_id = $request->get('category');
                $post->keywords = $request->get('keywords');
                $post->slug = $request->get('slug');
                $post->moderate = $this->user->can('premoderate_publ');
            } else {
                $post = new \Post([
                    'title'         => $request->get('title'),
                    'content'       => $request->get('content'),
                    'category_id'   => $request->get('category'),
                    'keywords'      => $request->get('keywords'),
                    'slug'          => $request->get('slug'),
                    'author_id'     => $this->user->id,
                    'moderate'      => $this->user->can('premoderate_publ')
                ]);
            }

            // Updating instance
            $post->save();

            if ( !$post->moderate ) {
                // Exporting to social
                static::export($post, $this->view);

                // Ping sitemap
                NCService::load('SocialMedia.Ping');
            }

            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        if ( $post instanceof \Post ) {
            $post = $post->to_array();
        }

        return $this->view->render('posts/create.twig', [
            'post'          => $post,
            'title'         => $title,
            'categories'    => \PostCategory::as_array(\PostCategory::all())
        ]);
    }
} 