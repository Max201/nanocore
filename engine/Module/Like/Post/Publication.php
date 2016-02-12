<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


class Publication implements BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function plus($postId, \User $author)
    {
        $publication = \Post::find(intval($postId));
        if ( !$publication ) {
            return false;
        }

        if ( $publication->author_id == $author->id ) {
            return $publication->author_id;
        }

        if ( $publication->author->rate(1) ) {
            return $publication->author_id;
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
        $publication = \Post::find(intval($postId));
        if ( !$publication ) {
            return false;
        }

        if ( $publication->author_id == $author->id ) {
            return $publication->author_id;
        }

        if ( $publication->author->rate(-1) ) {
            return $publication->author_id;
        }

        return false;
    }
} 