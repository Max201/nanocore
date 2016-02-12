<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Comment;


use Service\Paginator\Listing;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCService;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addPattern('post/<post:.+>', [$this, 'post'], 'add');
        $this->map->addPattern('delete', [$this, 'delete'], 'delete');
        $this->map->addPattern('list/<post:.+>', [$this, 'comments_list'], 'list');
    }

    static function globalize($module, $theme, $translate)
    {
        /*
         * Comments array
         */
        $theme->twig->addFilter(new \Twig_SimpleFilter('comments', function($post, $limit = 50, $reversed = false){
            // Find conditions
            $conditions = ['parent_id = ?', intval($post)];
            if ( !is_numeric($post) ) {
                $conditions = ['post = ?', $post];
            }

            // Comments
            $comments = \Comment::find('all', [
                'conditions' => $conditions,
                'limit'      => $limit,
                'order'      => 'created_at DESC'
            ]);

            $comments = \Comment::as_array($comments);
            return $reversed ? array_reverse($comments) : $comments;
        }));

        /*
         * Comments counter
         */
        $theme->twig->addFilter(new \Twig_SimpleFilter('comments_count', function($post){
            // Find conditions
            $conditions = ['parent_id = ?', intval($post)];
            if ( !is_numeric($post) ) {
                $conditions = ['post = ?', $post];
            }

            // Count comments
            return \Comment::count([
                'conditions' => $conditions
            ]);
        }));


        /*
         * Last 5 comments
         */
        $last_comments = function () {
            return \Comment::as_array(\Comment::find('all', [
                'order'    => 'created_at DESC',
                'limit'    => 5
            ]));
        };

        return [
            '_comm'  => lazy_arr(['$last' => $last_comments])
        ];
    }

    /**
     * Delete comment
     *
     * @param Request $request
     * @param $matches
     * @return mixed
     */
    public function delete(Request $request, $matches)
    {
        // Get comment
        $post = \Comment::find($request->get('comment'));

        // Assign com id
        if ( $post->parent_id ) {
            $this->view->assign('com', $post->parent->post);
        } else {
            $this->view->assign('com', $post->post);
        }

        // Delete comment
        if ( $request->isMethod('post') && $this->user->can('edit_comments') && $post ) {
            // Delete childs comments
            \Comment::table()->delete('parent_id = ' . $post->id);

            $post->delete();
            $this->view->assign('status', [
                'success'   => true,
                'message'   => $this->lang->translate('comment.post.deleted')
            ]);
        }

        return $this->comments_list($request, $matches);
    }

    /**
     * Add new comment
     *
     * @param Request $request
     * @param $matches
     * @return mixed|string
     */
    public function post(Request $request, $matches)
    {
        $comment = trim($request->get('comment'));
        $post = trim($matches->get('post'));
        $to = null;

        // Is that answer
        if ( is_numeric($post) ) {
            $to = \Comment::find(intval($post));

            if ( $post ) {
                $post = $to->post;
            }
        }

        // Assign com id
        $this->view->assign('com', $post);

        // Posting comment
        if ( $request->isMethod('post') ) {
            // Check comment
            if ( !$post || strlen($comment) < 2 ) {
                return static::json_response([
                    'error'     => 1,
                    'message'   => $this->lang->translate('comment.post.failed')
                ]);
            }

            // Check permissions
            if ( !$this->user->can('comment') ) {
                return static::json_response([
                    'error'     => 2,
                    'message'   => $this->lang->translate('comment.post.denied')
                ]);
            }

            // Create new comment
            $comment = new \Comment([
                'author_id' => $this->user->id,
                'parent_id' => $to instanceof \Comment ? $to->id : null,
                'post'      => $to instanceof \Comment ? $to->id : $post,
                'body'      => $comment
            ]);

            if ( $comment->save() ) {
                $status = [
                    'success'   => $comment->id,
                    'message'   => $this->lang->translate('comment.post.success'),
                ];
            } else {
                $status = [
                    'error'     => 3,
                    'message'   => $this->lang->translate('comment.post.failed'),
                ];
            }

            $this->view->assign('status', $status);
        }

        return $this->comments_list($request, $matches);
    }

    /**
     * Comments list
     *
     * @param $request
     * @param $matches
     * @return mixed
     */
    public function comments_list($request, $matches)
    {
        $post = trim($this->view->get('com', $matches->get('post')));
        $filter = [
            'conditions'    => ['post = ?', $post],
            'order'         => 'created_at DESC'
        ];

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Comment::count($filter)]);
        $filter = array_merge($filter, $paginator->limit());

        // Comments
        $comments = \Comment::find('all', $filter);
        $comments = \Comment::as_array($comments);

        // Context
        $context = [
            'com'       => $post,
            'comments'  => $comments,
            'page'      => $paginator->cur_page,
            'listing'   => $paginator->pages()
        ];

        // Add comment status
        if ( $status = $this->view->get('status', false) ) {
            $context['status'] = $status;
        }

        // Build response
        if ( $request->get('type', 'html') == 'json') {
            unset($context['listing']);
            $context['pages'] = $paginator->pages;
            $context['rows'] = $paginator->num_rows;

            return static::json_response($context);
        }

        return $this->view->render('comment/list.twig', $context);
    }
} 