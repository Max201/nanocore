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
        $this->map->addRoute('create', [$this, 'create_page'], 'page.new');
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

    public function create_page($request, $matches)
    {


        return $this->view->render('pages/create.twig', [
            'title'         => $this->lang->translate('page.create')
        ]);
    }
} 