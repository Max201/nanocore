<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like;


use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    static $fa_icon = 'thumbs-o-up';
    static $menu = [
        'like.list' => '/control/comment/',
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'comments_list'], 'list');
    }

    public function comments_list($request)
    {
        // Delete page
        if ( $request->get('delete') ) {
            $delete = $request->get('delete');
            if ( $delete != 'all' ) {
                $comment = \Comment::find_by_id(intval($delete));

                if ( $comment ) {
                    // Delete childs comments
                    \Comment::table()->delete('parent_id = ' . $comment->id);

                    if ( $comment->delete() ) {
                        $this->view->assign('message', $this->lang->translate('form.deleted'));
                    }
                }
            } else {
                \Comment::table()->delete('1');
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter
        $filter = [];
        if ( $request->get('author') ) {
            $author = \User::find($request->get('author'));
            if ( $author ) {
                $filter['conditions'] = ['author_id = ?', $author->id];
            }
        }

        $filter['order'] = 'id DESC';
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Comment::count('all')]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter users
        $comments = \Comment::all($filter);
        $comments = \Comment::as_array($comments);
        return $this->view->render('comment/list.twig', [
            'title'         => $this->lang->translate('comment.list'),
            'comments_list' => $comments,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }
} 