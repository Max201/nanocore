<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Page;


use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;


class Control extends NCControl
{
    static $fa_icon = 'file';
    static $menu = [
        'page.list' => '/control/page/',
    ];

    public function route()
    {
        $this->map->addRoute('/', [$this, 'pages_list'], 'list');
        $this->map->addRoute('create', [$this, 'edit_page'], 'page.new');
        $this->map->addPattern('edit/<id:\d+?>', [$this, 'edit_page'], 'page.edit');
    }

    public function pages_list($request)
    {
        // Delete page
        if ( $request->get('delete') ) {
            $page = \Page::find_by_id(intval($request->get('delete')));
            if ( $page && $page->delete() ) {
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
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Page::count('all')]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter users
        $pages = \Page::all($filter);
        $pages = array_map(function($i){ return $i->to_array(); }, $pages);
        return $this->view->render('pages/list.twig', [
            'title'         => $this->lang->translate('page.list'),
            'pages_list'    => $pages,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function edit_page(Request $request, $matches)
    {
        $title = $this->lang->translate('page.create');

        // Get page for updating
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $page = \Page::find_by_id($id);
            $title = $this->lang->translate('page.editing', $page->title);
        } else {
            $page = [
                'title'     => $this->lang->translate('page.name'),
                'content'   => '',
                'template'  => 'default.twig'
            ];
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $page instanceof \Page ) {
                $page->title = $request->get('title');
                $page->content = $request->get('content');
                $page->slug = $request->get('slug');
                $page->template = $request->get('template');
            } else {
                $page = new \Page([
                    'title'     => $request->get('title'),
                    'content'   => $request->get('content'),
                    'slug'      => $request->get('slug'),
                    'template'  => $request->get('template'),
                    'author_id' => $this->user->id
                ]);

                // Ping sitemap
                NCService::load('SocialMedia.Ping');
            }

            // Updating instance
            $page->save();
            $page = $page->to_array();


            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        return $this->view->render('pages/create.twig', [
            'page'          => $page,
            'title'         => $title,
            'templates'     => $this->view->list_files('pages', '@current')
        ]);
    }
} 