<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Forum;


use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    static $fa_icon = 'comments';
    static $menu = [
        [
            'title' => 'forum.list',
            'href'  => '/control/forum/',
            'counter' => 'total'
        ]
    ];

    static function total()
    {
        return \Forum::count();
    }

    public function route()
    {
        $this->map->addRoute('/', [$this, 'topic_list'], 'list');
        $this->map->addRoute('create', [$this, 'edit_topic'], 'topic.new');
        $this->map->addPattern('edit/<id:\d+?>', [$this, 'edit_topic'], 'topic.edit');
        $this->map->addPattern('subjects/<id:\d+?>', [$this, 'subject_list'], 'list.subjects');
    }

    public function subject_list($request, $matches)
    {
        $title = $this->lang->translate('forum.themes');
        $root = \Forum::find($matches->get('id'));
        if ( !$root ) {
            return static::redirect_response('/control/forum/');
        }

        $title .= ' "' . $root->title . '"';

        // Delete topic
        if ( $request->get('delete') ) {
            $forum = \ForumTheme::find_by_id(intval($request->get('delete')));
            if ( $forum ) {
                $forum->delete();
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter
        $filter = [
            'conditions' => ['forum_id = ?', $root->id]
        ];

        if ( $request->get('author') ) {
            $author = \User::find($request->get('author'));
            if ( $author ) {
                $this->view->assign('author', $author->id);
                $title .= ', ' . $author->username;
                $filter['conditions'] = ['author_id = ? AND forum_id = ?', $author->id, $root->id];
            }
        }

        $filter['order'] = 'id DESC';
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \ForumTheme::count('all')]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter subjects
        $subjects = \ForumTheme::as_array(\ForumTheme::all($filter));
        return $this->view->render('forum/list_subjects.twig', [
            'title'         => $title,
            'subjects_list' => $subjects,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function topic_list($request)
    {
        $title = $this->lang->translate('forum.list');

        // Delete topic
        if ( $request->get('delete') ) {
            $forum = \Forum::find_by_id(intval($request->get('delete')));
            if ( $forum ) {
                $forum->delete();
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter
        $filter = [
            'conditions' => ['forum_id < 1']
        ];

        if ( $request->get('author') ) {
            $author = \User::find($request->get('author'));
            if ( $author ) {
                $this->view->assign('author', $author->id);
                $title .= ' "' . $author->username . '"';
                $filter['conditions'] = ['author_id = ?', $author->id];
            }
        }

        // Parent
        if ( $request->get('parent') ) {
            $parent = \Forum::find($request->get('parent'));
            if ( $parent ) {
                $this->view->assign('parent', $parent->id);
                $title .= ' "' . $parent->title . '"';
                $filter['conditions'] = ['forum_id = ?', $parent->id];
            }
        }

        $filter['order'] = 'id DESC';
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Forum::count('all')]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter topics
        $forums = \Forum::as_array(\Forum::all($filter));
        return $this->view->render('forum/list.twig', [
            'title'         => $title,
            'forums_list'   => $forums,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function subjects(Request $request, $matches)
    {
        $title = $this->lang->translate('forum.list');

        // Delete topic
        if ( $request->get('delete') ) {
            $forum = \Forum::find_by_id(intval($request->get('delete')));
            if ( $forum ) {
                $forum->delete();
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter
        $filter = [
            'conditions' => ['forum_id < 1']
        ];

        if ( $request->get('author') ) {
            $author = \User::find($request->get('author'));
            if ( $author ) {
                $filter['conditions'] = ['author_id = ?', $author->id];
            }
        }

        // Parent
        if ( $request->get('parent') ) {
            $parent = \Forum::find($request->get('parent'));
            if ( $parent ) {
                $filter['conditions'] = ['forum_id = ?', $parent->id];
            }
        }

        $filter['order'] = 'id DESC';
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Forum::count('all')]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter topics
        $forums = \Forum::as_array(\Forum::all($filter));
        return $this->view->render('forum/list.twig', [
            'title'         => $title,
            'forums_list'   => $forums,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function edit_topic(Request $request, $matches)
    {
        $title = $this->lang->translate('forum.create');

        // Get page for updating
        $id = intval($matches->get('id', $request->get('id')));

        // Parent topic
        $topics = \Forum::as_array(\Forum::find('all', ['conditions' => ['forum_id = 0 AND id <> ?', $id]]));

        if ( $id > 0 ) {
            $forum = \Forum::find_by_id($id);
            $title = $this->lang->translate('forum.editing', $forum->title);
        } else {
            $forum = [
                'title'     => $this->lang->translate('page.name'),
                'forum_id'  => null
            ];
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $forum instanceof \Forum ) {
                $forum->title = $request->get('title');
                $forum->forum_id = $request->get('forum');
                $forum->author_id = $this->user->id;
            } else {
                $forum = new \Forum([
                    'title'     => $request->get('title'),
                    'forum_id'  => $request->get('forum'),
                    'author_id' => $this->user->id
                ]);
            }

            // Updating instance
            $forum->save();
            $forum = $forum->to_array();


            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        return $this->view->render('forum/create.twig', [
            'forum'         => $forum,
            'title'         => $title,
            'topics'        => $topics
        ]);
    }
} 