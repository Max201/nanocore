<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin\Wall;


use Symfony\Component\HttpFoundation\Request;
use System\Environment\Env;


/**
 * Class CMSWall
 * @package Module\Admin\Wall
 */
trait CMSWall
{
    /**
     * @param Request $request
     * @param null $matches
     * @return mixed
     */
    public function wall_ips(Request $request, $matches = null)
    {
        // Ban ip
        if ( $request->get('ban') ) {
            Env::$kernel->ipwall->add(trim($request->get('ban')));
            if ( Env::$kernel->ipwall->save() ) {
                $this->view->assign('message', $this->lang->translate('admin.ip.banned_successfully', $request->get('ban')));
            } else {
                $this->view->assign('message', $this->lang->translate('admin.ip.banned_failed'));
            }
        }

        // Unban ip
        if ( $request->get('unban') ) {
            Env::$kernel->ipwall->del(trim($request->get('unban')));
            if ( Env::$kernel->ipwall->save() ) {
                $this->view->assign('message', $this->lang->translate('admin.ip.unbanned_successfully', $request->get('ban')));
            } else {
                $this->view->assign('message', $this->lang->translate('admin.ip.unbanned_failed'));
            }
        }

        return $this->view->render('dashboard/ipwall.twig', [
            'title'     => $this->lang->translate('admin.cms_wall'),
            'banned'    => Env::$kernel->ipwall->banned()
        ]);
    }
} 