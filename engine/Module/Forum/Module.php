<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Forum;


use Service\Paginator\Listing;
use Service\SocialMedia\Ping;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Engine\NCService;
use System\Engine\NCSitemapBuilder;
use System\Environment\Env;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    /**
     * Enable module sitemap
     */
    const SITEMAP = true;

    /**
     * Routing
     */
    public function route()
    {
        $this->map->addRoute('/', [$this, 'forum'], 'forum');
        $this->map->addPattern('topic/new/<id:\d+>', [$this, 'new_topic'], 'newtop');
        $this->map->addPattern('subject/<id:\d+>', [$this, 'subject'], 'subject');
        $this->map->addPattern('topic/<id:\d+>', [$this, 'topic'], 'topic');

        $this->map->addPattern('post/<topic:.+>', [$this, 'post'], 'add');
        $this->map->addPattern('delete', [$this, 'delete'], 'delete');
        $this->map->addPattern('list/<topic:.+>', [$this, 'posts_list'], 'list');
    }

    /**
     * Generate sitemap
     *
     * @param NCSitemapBuilder $builder
     * @return NCSitemapBuilder
     */
    public function sitemap(NCSitemapBuilder $builder)
    {
        // Topics
        $forums = \ForumTheme::all();
        foreach ( $forums as $topic ) {
            $builder->add_url(
                $this->map->reverse('topic', [$topic->id]),
                0.9,
                $topic->updated_at,
                'daily'
            );
        }

        return $builder;
    }

    /**
     * @param NCModule $module
     * @param \Service\Render\Theme $theme
     * @param \Service\Application\Translate $translate
     * @return array|\System\Engine\NCBlock[]
     */
    static function globalize($module, $theme, $translate)
    {
        /*
         * Last 5 topics
         */
        $last_topics = function () {
            $topics = \ForumTheme::as_array(\ForumTheme::find('all', [
                'conditions' => ['active = 1'],
                'order'    => 'updated_at DESC',
                'limit'    => 5
            ]));

            $topics = array_map(
                function($item) {
                    $item['link'] = '/forum/topic/' . $item['id'];
                    return $item;
                },
                $topics
            );

            return $topics;
        };

        return [
            '_forum'  => lazy_arr([
                '$last' => $last_topics
            ])
        ];
    }

    /**
     * Forums list
     *
     * @param Request $request
     * @param null $matches
     * @return mixed
     */
    public function forum(Request $request, $matches = null)
    {
        $forums_list = \Forum::find('all', [
            'conditions'    => ['forum_id < 1']
        ]);

        // Make array and get child subjects
        $forums_list = \Forum::as_array($forums_list);
        $forums_list = array_map(function($item){
            $item['subjects'] = \Forum::as_array(\Forum::find('all', [
                'conditions'    => ['forum_id = ?', $item['id']]
            ]));

            return $item;
        }, $forums_list);

        return $this->view->render('forum/forum.twig', [
            'title'         => $this->lang->translate('forum.title'),
            'forums_list'   => $forums_list
        ]);
    }

    /**
     * Subject topics
     */
    public function subject(Request $request, $matches)
    {
        $forum = \Forum::find($matches->get('id'));
        if ( !$forum ) {
            return $this->error404($request);
        }

        // Assign data
        $this->view->assign('author', $forum->author->to_array());
        $this->view->assign('subject', $forum->to_array());

        // Manage forum
        if ( $this->user && $this->user->can('manage_forum') ) {
            // Delete topic
            if ( $request->get('delete') ) {
                $delete = \ForumTheme::find($request->get('delete'));
                if ( $delete->delete() ) {
                    $this->view->assign('success', $this->lang->translate('forum.post.deleted'));
                } else {
                    $this->view->assign('error', $this->lang->translate('forum.post.delete_failed'));
                }
            }

            // Close
            if ( $request->get('close') ) {
                $close = \ForumTheme::find($request->get('close'));
                $close->close($this->user);
            }

            // Open
            if ( $request->get('open') ) {
                $open = \ForumTheme::find($request->get('open'));
                $open->open();
            }

            // Pin
            if ( $request->get('pin') ) {
                $pin = \ForumTheme::find($request->get('pin'));
                $pin->pin();
            }

            // Unpin
            if ( $request->get('unpin') ) {
                $unpin = \ForumTheme::find($request->get('unpin'));
                $unpin->unpin();
            }

            // Redirect
            if ( $request->get('next') ) {
                return static::redirect_response($request->get('next'));
            }
        }

        // Send date headers
        Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $forum->updated_at));

        // Topics filter
        $filter = [
            'conditions'    => ['forum_id = ?', $forum->id],
            'order'         => 'priority DESC, active ASC, updated_at DESC, created_at DESC'
        ];

        // Paginator
        $total_rows = \ForumTheme::count(['conditions' => $filter['conditions']]);
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, $total_rows]);
        $filter = array_merge($filter, $paginator->limit());

        // Subject topics
        $topics_list = \ForumTheme::as_array(\ForumTheme::find('all', $filter));
        return $this->view->render('forum/topics.twig', [
            'title'         => $forum->title,
            'topics_list'   => $topics_list,
            'listing'       => $paginator->pages(),
            'page'          => $request->page
        ]);
    }

    /**
     * @param Request $request
     * @param $matches
     * @return mixed
     */
    public function new_topic(Request $request, $matches)
    {
        if ( $access = $this->authenticated_only() ) {
            return $access;
        }

        $subject = $matches->get('id');
        $subject = \Forum::find($subject);

        if ( !$subject || !$subject->forum_id ) {
            return $this->error404($request);
        }

        if ( $request->isMethod('post') && $this->user->can('write_forum') ) {
            $data = [
                'description'   => trim($request->get('edit')),
                'title'         => trim($request->get('title')),
                'author_id'     => $this->user->id,
                'forum_id'      => $subject->id
            ];

            $this->view->assign('data', $data);
            $errors = [];

            if ( strlen($data['title']) < 3 ) {
                $errors[] = $this->lang->translate('forum.topic.title_len');
            }

            if ( strlen($data['description']) < 3 ) {
                $errors[] = $this->lang->translate('forum.topic.body_len');
            }

            if ( count($errors) ) {
                $this->view->assign('errors', $errors);
            } else {
                $topic = new \ForumTheme($data);
                if ( $topic->save() ) {
                    /** @var Ping $ping */
                    $ping = NCService::load('SocialMedia.Ping');
                    return static::redirect_response($this->map->reverse('topic', ['id' => $topic->id]));
                } else {
                    $this->view->assign('errors', [$this->lang->translate('forum.topic.new_failed')]);
                }
            }
        }

        return $this->view->render('forum/create.twig', [
            'title' => $this->lang->translate('forum.topic.new'),
            'subject' => $subject->to_array()
        ]);
    }

    /**
     * List posts
     *
     * @param Request $request
     * @param $matches
     * @return string
     */
    public function topic(Request $request, $matches)
    {
        $topic = \ForumTheme::find($matches->get('id'));
        if ( !$topic ) {
            return $this->error404($request);
        }

        // Assign topic data
        $this->view->assign('topic', $topic->to_array());

        // Send date headers
        Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $topic->updated_at));

        // Topics filter
        $filter = [
            'conditions'    => ['theme_id = ?', $topic->id],
            'order'         => 'created_at DESC'
        ];

        // Paginator
        $total_rows = \ForumPost::count(['conditions' => $filter['conditions']]);
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, $total_rows]);
        $filter = array_merge($filter, $paginator->limit());

        // Topic posts
        $topics_list = \ForumPost::as_array(\ForumPost::find('all', $filter));
        return $this->view->render('forum/topic.twig', [
            'title'         => $topic->title,
            'posts_list'    => $topics_list,
            'listing'       => $paginator->pages(),
            'page'          => $request->page
        ]);
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
        $post = \ForumPost::find($request->get('post'));

        // Assign topic
        if ( $post ) {
            $this->view->assign('topic', $post->theme);
        }

        // Delete post
        if ( $request->isMethod('post') && $this->user->can('manage_forum') && $post ) {
            $post->delete();
            $this->view->assign('status', [
                'success'   => true,
                'message'   => $this->lang->translate('forum.post.deleted')
            ]);
        }

        return $this->posts_list($request, $matches);
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
        $topic = \ForumTheme::find($matches->get('topic'));

        // Assign com id
        $this->view->assign('topic', $topic);

        // Posting comment
        if ( $request->isMethod('post') ) {
            // Check comment
            if ( !$topic || strlen($comment) < 2 || !$topic->active ) {
                return static::json_response([
                    'error'     => 1,
                    'message'   => $this->lang->translate('forum.post.failed')
                ]);
            }

            // Check permissions
            if ( !$this->user->can('write_forum') ) {
                return static::json_response([
                    'error'     => 2,
                    'message'   => $this->lang->translate('forum.post.denied')
                ]);
            }

            // Create new comment
            $comment = new \ForumPost([
                'author_id' => $this->user->id,
                'theme_id'  => $topic->id,
                'content'   => $comment
            ]);

            if ( $comment->save() ) {
                $status = [
                    'success'   => $comment->id,
                    'message'   => $this->lang->translate('forum.post.success'),
                ];
            } else {
                $status = [
                    'error'     => 3,
                    'message'   => $this->lang->translate('forum.post.failed'),
                ];
            }

            $this->view->assign('status', $status);
        }

        return $this->posts_list($request, $matches);
    }

    /**
     * Comments list
     *
     * @param $request
     * @param $matches
     * @return mixed
     */
    public function posts_list($request, $matches)
    {
        $topic = $this->view->get('topic', $matches->get('topic'));
        $topic = $topic instanceof \ForumTheme  ? $topic : \ForumTheme::find($topic);
        $filter = [
            'conditions'    => ['theme_id = ?', $topic->id],
            'order'         => 'created_at DESC'
        ];

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \ForumPost::count($filter)]);
        $filter = array_merge($filter, $paginator->limit());

        // Comments
        $comments = \ForumPost::find('all', $filter);
        $comments = \ForumPost::as_array($comments);

        // Context
        $context = [
            'topic'     => $topic,
            'posts_list'=> $comments,
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

        return $this->view->render('forum/posts.twig', $context);
    }
} 