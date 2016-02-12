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

        if ( $page->author_id == $author->id ) {
            return $page->author_id;
        }

        if ( $page->author->rate(1) ) {
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
        if ( !$page  ) {
            return false;
        }

        if ( $page->author_id == $author->id ) {
            return $page->author_id;
        }

        if ( $page->author->rate(-1) ) {
            return $page->author_id;
        }

        return false;
    }
} 