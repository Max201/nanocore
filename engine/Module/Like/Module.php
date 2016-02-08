<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like;


use Module\Like\Post\BaseLike;
use Service\Paginator\Listing;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCService;


/**
 * Class Module
 * @package Module\Like
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addPattern('post/<post:.+>', [$this, 'post'], 'like');
        $this->map->addPattern('list/<post:.+>', [$this, 'likes_list'], 'list');
    }

    static function globalize($module, $theme, $translate)
    {
        /*
         * Likes array
         */
        $theme->twig->addFilter(new \Twig_SimpleFilter('likes', function($post, $limit = 5, $reversed = false){
            // Likes
            $likes = \Like::find('all', [
                'conditions' => ['post = ?', $post],
                'limit'      => $limit,
                'order'      => 'created_at DESC'
            ]);

            $likes = \Like::as_array($likes);
            return $reversed ? array_reverse($likes) : $likes;
        }));

        /*
         * Likes counter
         */
        $theme->twig->addFilter(new \Twig_SimpleFilter('likes_count', function($post){
            $count = \Like::rating($post);
            if ( $count ) {
                return $count;
            }

            return 0;
        }));

        return [];
    }

    /**
     * Parse like data
     *
     * @param $post
     * @return array
     */
    static function parse_data($post)
    {
        $vote = [
            'method'    => 'plus',
            'app'       => null,
            'id'        => null
        ];

        // Plus or minus
        if ( $post[0] == '-' ) {
            $vote['method'] = 'minus';
        }

        // Parse post id
        preg_match('/([a-z]+?)([0-9]+)/i', $post, $matches);
        if ( isset($matches[1]) ) {
            $vote['app'] = ucfirst(strtolower($matches[1]));
        }

        if ( isset($matches[2]) ) {
            $vote['id'] = intval($matches[2]);
        }

        return $vote;
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
        $post = trim($matches->get('post'));
        // Check like
        if ( !$post || strlen($post) < 2 ) {
            return static::json_response([
                'error'     => 1,
                'message'   => $this->lang->translate('like.post.failed')
            ]);
        }

        // Parse vote data
        $vote = static::parse_data($post);
        $post = ltrim($post, '-+');
        $this->view->assign('like_post', $post);
        $this->view->assign('like_vote', $vote);

        // Posting Like
        if ( $request->isMethod('post') ) {
            // Check permissions
            if ( !$this->user->can('like') || !$this->user->id ) {
                return static::json_response([
                    'error'     => 1,
                    'message'   => $this->lang->translate('like.post.denied')
                ]);
            }

            $module = '\\Module\\Like\\Post\\' . $vote['app'];
            if ( !class_exists($module) || !method_exists($module, $vote['method']) ) {
                return static::json_response([
                    'error'     => 4,
                    'message'   => $this->lang->translate('like.post.failed')
                ]);
            }

            // Check existings votes
            if ( \Like::count(['conditions' => ['post = ? AND author_id = ?', $post, $this->user->id]]) ) {
                $likes = \Like::find([
                    'conditions'    => ['post = ? AND author_id = ?', $post, $this->user->id]
                ]);

                if ( $likes->vote == 1 && $vote['method'] == 'plus' || $likes->vote == -1 && $vote['method'] == 'minus' ) {
                    return static::json_response([
                        'error'     => 2,
                        'message'   => $this->lang->translate('like.post.exists')
                    ]);
                } else {
                    $likes->delete();
                    if ( $vote['method'] == 'plus' ) {
                        call_user_func($module . '::' . 'plus', $vote['id'], $this->user);
                    } else {
                        call_user_func($module . '::' . 'minus', $vote['id'], $this->user);
                    }
                }
            }

            // Check vote module
            if ( !$vote['id'] || !$vote['app'] ) {
                return static::json_response([
                    'error'     => 3,
                    'message'   => $this->lang->translate('like.post.failed')
                ]);
            }

            // Voted in module
            if ( !($user_id = call_user_func($module . '::' . $vote['method'], $vote['id'], $this->user)) ) {
                return static::json_response([
                    'error'     => 5,
                    'message'   => $this->lang->translate('like.post.failed')
                ]);
            }

            // Add new vote
            $like = new \Like([
                'author_id' => $this->user->id,
                'user_id'   => $user_id,
                'vote'      => $vote['method'] == 'plus' ? '1' : '-1',
                'post'      => $post
            ]);

            if ( !$like->save() ) {
                return static::json_response([
                    'error'     => 3,
                    'message'   => $this->lang->translate('like.post.failed')
                ]);
            } else {
                return static::json_response([
                    'success'   => \Like::rating($post),
                    'message'   => $this->lang->translate('like.post.success')
                ]);
            }
        }

        return $this->likes_list($request, $matches);
    }

    /**
     * Comments list
     *
     * @param $request
     * @param $matches
     * @return mixed
     */
    public function likes_list($request, $matches)
    {
        $post = trim($matches->get('post'), '-+ ');
        $filter = [
            'conditions'    => ['post = ?', $post],
            'order'         => 'created_at DESC'
        ];

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Like::count($filter)]);
        $filter = array_merge($filter, $paginator->limit());

        // Likes
        $likes = \Like::find('all', $filter);
        $likes = \Like::as_array($likes);

        // Context
        $context = [
            'rating'    => \Like::rating($post),
            'likes'     => $likes,
            'page'      => $paginator->cur_page,
            'listing'   => $paginator->pages()
        ];

        // Build response
        if ( $request->get('type', 'html') == 'json') {
            unset($context['listing']);
            $context['pages'] = $paginator->pages;
            $context['rows'] = $paginator->num_rows;

            return static::json_response($context);
        }

        return $this->view->render('likes/list.twig', $context);
    }
} 