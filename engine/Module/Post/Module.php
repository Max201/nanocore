<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Post;


use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCModule;
use System\Environment\Env;
use System\Util\Calendar;
use User;


/**
 * Class Module
 * @package Module\Post
 */
class Module extends NCModule
{
    public function route()
    {
        $this->map->addPattern('<id:\d+>-<slug:.+>.html', [$this, 'post'], 'post');
    }

    /**
     * User registration page
     */
    public function post(Request $request, $matches)
    {
        try {
            $post = \Post::find_by_id($matches->get('id'));
            Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $post->updated_at));
            return $this->view->render('posts/default.twig', [
                'post'  => $post->to_array(),
                'title' => $post->title,
            ]);
        } catch (\Exception $e) {
            die($e->getMessage());
            $this->error404($request);
        }
    }
} 