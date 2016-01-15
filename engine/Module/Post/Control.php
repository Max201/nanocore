<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Post;


use Service\SocialMedia\SocialMedia;
use Service\SocialMedia\Twitter;
use Service\SocialMedia\Vkontakte;
use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    static $fa_icon = 'rss';
    static $menu = [
        'post.list' => '/control/post/',
        'post.categories' => '/control/post/categories/',
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'posts_list'], 'list');
        $this->map->addRoute('categories', [$this, 'posts_categories'], 'list.categories');
        $this->map->addRoute('categories/edit', [$this, 'edit_category'], 'post.category_new');
        $this->map->addRoute('create', [$this, 'edit_post'], 'post.new');

        $this->map->addPattern('edit/<id:\d+?>', [$this, 'edit_post'], 'post.edit');
    }

    public function edit_category(Request $request)
    {
        // Looking for category
        $category = null;
        if ( $request->get('id') ) {
            $category = \PostCategory::find_by_id($request->get('id'));
        }

        if ( !$category ) {
            $category = [
                'id'            => null,
                'title'         => $this->lang->translate('post.category'),
                'parent_id'     => null,
            ];
        }


        // Loading possible social postings
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        $posting = [];
        // VKontakte
        /** @var Vkontakte $vk */
        $vk = $smp->get_manager('vk');
        if ( $vk->configured() && $vk->active() ) {
            $posting['vk'] = $vk->m_groups();
        }

        /** @var Twitter $tw */
        $tw = $smp->get_manager('tw');
        if ( $tw->configured() && $tw->active() ) {
            $posting['tw'] = true;
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $category instanceof \PostCategory ) {
                $category->title = $request->get('title');
                $category->parent_id = $request->get('parent_id');
                $category->post_vkontakte = $request->get('post_vkontakte');
                $category->post_twitter = $request->get('post_twitter');
                $category->post_facebook = $request->get('post_facebook');
            } else {
                $category = new \PostCategory([
                    'title'             => $request->get('title'),
                    'parent_id'         => $request->get('parent_id'),
                    'post_vkontakte'    => $request->get('post_vkontakte'),
                    'post_twitter'      => $request->get('post_twitter'),
                    'post_facebook'     => $request->get('post_facebook')
                ]);
            }

            // Updating instance
            $category->save();


            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        if ( $category instanceof \PostCategory ) {
            $category = $category->to_array();
        }

        if ( !$category['id'] ) {
            $categories = \PostCategory::all();
        } else {
            $categories = \PostCategory::all([
                'conditions'    => ['id <> ?', $category['id']]
            ]);
        }

        return $this->view->render('posts/create_category.twig', [
            'category'      => $category,
            'posting'       => $posting,
            'title'         => $this->lang->translate('post.category_new'),
            'categories'    => array_map(function($i){ return $i->to_array(); }, $categories)
        ]);
    }

    public function posts_categories(Request $request)
    {
        $title = $this->lang->translate('post.categories');

        // Delete post
        if ( $request->get('delete') ) {
            $post = \PostCategory::find_by_id(intval($request->get('delete')));
            if ( $post && $post->delete() ) {
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter categories
        $filter = [];
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        if ( $request->get('category') ) {
            $category = \PostCategory::find($request->get('category'));
            $filter['conditions'] = ['parent_id = ?', $category->id];
            $title = $this->lang->translate('post.category') . ' ' . $category->title;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \PostCategory::count()]);
        $filter = array_merge($filter, $paginator->limit());

        // Listing categories
        $categories = \PostCategory::all($filter);
        $categories = array_map(function($i){ $a = $i->to_array(); $a['posts'] = \Post::count(['conditions' => ['category_id = ?', $a['id']]]); return $a; }, $categories);
        return $this->view->render('posts/list_categories.twig', [
            'title'         => $title,
            'category_list' => $categories,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function posts_list(Request $request)
    {
        $title = $this->lang->translate('post.list');

        // Delete post
        if ( $request->get('delete') ) {
            $post = \Post::find_by_id(intval($request->get('delete')));
            if ( $post && $post->delete() ) {
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter
        $filter = [];
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        $category = null;
        if ( $request->get('category') ) {
            $category = \PostCategory::first($request->get('category'));
            if ( $category ) {
                $title = $this->lang->translate('post.category') . ' ' . $category->title;
                $filter['conditions'] = ['category_id = ?', $category->id];
            }
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Page::count()]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter users
        $posts = \Post::all($filter);
        $posts = array_map(function($i){ return $i->to_array(); }, $posts);
        return $this->view->render('posts/list.twig', [
            'title'         => $title,
            'posts_list'    => $posts,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function edit_post(Request $request, $matches)
    {
        $title = $this->lang->translate('post.create');

        // Get post for updating
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $post = \Post::find_by_id($id);
            $title = $this->lang->translate('post.editing', $post->title);
        } else {
            $post = [
                'title'     => $this->lang->translate('post.name'),
                'content'   => '',
                'category'  => \PostCategory::last()->to_array()
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
            } else {
                $post = new \Post([
                    'title'         => $request->get('title'),
                    'content'       => $request->get('content'),
                    'category_id'   => $request->get('category'),
                    'keywords'      => $request->get('keywords'),
                    'slug'          => $request->get('slug'),
                    'author_id'     => $this->user->id
                ]);
            }

            // Updating instance
            $post->save();

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
            'categories'    => array_map(function($i){ return $i->to_array(); }, \PostCategory::all())
        ]);
    }
} 