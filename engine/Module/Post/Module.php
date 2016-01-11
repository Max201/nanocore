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

    public static function clear_body($post)
    {
        if ( $post instanceof \Post ) {
            $post = $post->asArrayFull();
        }

        // Search attachements
        preg_match_all('#<img.+?src="(.+?)"#', $post['content'], $m);
        $images = [];
        if ( $m ) {
            foreach ( $m[1] as $img ) {
                if ( $img[0] == '/' ) {
                    $img = Env::$request->getSchemeAndHttpHost() . $img;
                }

                $images[] = $img;
            }
        }

        $post['images'] = $images;
        return $post;
    }

    /**
     * User registration page
     */
    public function post(Request $request, $matches)
    {
        $post = \Post::find_by_id($matches->get('id'));
        if ( $post ) {
            Env::$response->headers->set('Last-Modified', date('D, d M Y H:i:s \G\M\T', $post->updated_at));
            return $this->view->render('posts/default.twig', [
                'post'  => $post->asArrayFull(),
                'title' => $post->title,
            ]);
        }

        return $this->error404($request);
    }
} 