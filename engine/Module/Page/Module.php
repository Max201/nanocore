<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Page;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Util\Calendar;
use User;


/**
 * Class Module
 * @package Module\User
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addPattern('<id:\d+>-<slug:.+>.html', [$this, 'page'], 'page');
    }

    /**
     * User registration page
     */
    public function page(Request $request, $matches)
    {
        try {
            $page = \Page::find_by_id($matches->get('id'));
            return $this->view->render('page/default.twig', ['page'=>$page->to_array()]);
        } catch (\Exception $e) {
            return $this->error404($request);
        }
    }
} 