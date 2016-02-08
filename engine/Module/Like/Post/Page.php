<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


class Page implements BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function plus($postId, \User $author)
    {
        $page = \Page::find(intval($postId));
        if ( !$page ) {
            return false;
        }

        $page->author->rating = $page->author->rating + 1;
        if ( $page->author->save() ) {
            return $page->author_id;
        }

        return false;
    }

    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function minus($postId, \User $author)
    {
        $page = \Page::find(intval($postId));
        if ( !$page ) {
            return false;
        }

        $page->author->rating = $page->author->rating - 1;
        if ( $page->author->save() ) {
            return $page->author_id;
        }

        return false;
    }
} 