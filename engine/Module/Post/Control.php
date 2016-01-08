<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Post;


use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    static $fa_icon = 'rss';
    static $menu = [
        'post.list' => '/control/post/',
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'posts_list'], 'list');
        $this->map->addRoute('create', [$this, 'edit_post'], 'post.new');
        $this->map->addPattern('edit/<id:\d+?>', [$this, 'edit_post'], 'post.edit');
    }

    public function posts_list(Request $request)
    {
        // Delete post
        if ( $request->get('delete') ) {
            try {
                $post = \Post::find_by_id(intval($request->get('delete')));
                if ( $post && $post->delete() ) {
                    $this->view->assign('message', $this->lang->translate('form.deleted'));
                } else {
                    $this->view->assign('message', $this->lang->translate('form.delete_failed'));
                }
            } catch ( \Exception $e ) {
                $this->view->assign('message', $this->lang->translate('form.delete_failed'));
            }
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Page::count()]);
        $filter = $paginator->limit();

        // Filter users
        $posts = \Post::all($filter);
        $posts = array_map(function($i){ return $i->asArrayFull(); }, $posts);
        return $this->view->render('posts/list.twig', [
            'title'         => $this->lang->translate('post.list'),
            'posts_list'    => $posts,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function edit_post(Request $request, $matches)
    {
        // Get post for updating
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $post = \Post::find_by_id($id);
        } else {
            $post = [
                'title'     => $this->lang->translate('post.name'),
                'content'   => ''
            ];
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $post instanceof \Post ) {
                $post->title = $request->get('title');
                $post->content = $request->get('content');
                $post->slug = $request->get('slug');
            } else {
                $post = new \Post([
                    'title'     => $request->get('title'),
                    'content'   => $request->get('content'),
                    'slug'      => $request->get('slug'),
                    'author_id' => $this->user->id
                ]);
            }

            // Updating instance
            $post->save();
            $post = $post->to_array();


            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        return $this->view->render('posts/create.twig', [
            'post'          => $post,
            'title'         => $this->lang->translate('post.create'),
        ]);
    }
} 