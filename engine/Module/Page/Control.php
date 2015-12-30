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
use System\Engine\NCWidget;
use System\Environment\Env;


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
        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->get('page', 1), \Page::count()]);
        $filter = $paginator->limit();

        // Filter users
        $pages = \Page::all($filter);
        $pages = array_map(function($i){ return $i->asArrayFull(); }, $pages);
        return $this->view->render('pages/list.twig', [
            'title'         => $this->lang->translate('page.list'),
            'pages_list'    => $pages,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    public function edit_page(Request $request, $matches)
    {
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $page = \Page::find_by_id($id)->to_array();
        } else {
            $page = [
                'title'     => $this->lang->translate('page.name'),
                'content'   => '...'
            ];
        }

        // Create page
        if ( $request->isMethod('post') ) {
            if ( !$id ) {
                $page = new \Page([
                    'title'     => $request->get('title'),
                    'content'   => $request->get('content'),
                    'slug'      => $request->get('slug'),
                    'author_id' => $this->user->id
                ]);
            } else {
                $page = \Page::find_by_id($id);
                $page->title = $request->get('title');
                $page->content = $request->get('content');
                $page->slug = $request->get('slug');
            }

            $page->save();
            $page = $page->to_array();
        }

        return $this->view->render('pages/create.twig', [
            'page'          => $page,
            'title'         => $this->lang->translate('page.create'),
        ]);
    }
} 