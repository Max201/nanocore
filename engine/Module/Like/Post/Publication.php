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

        $publication->author->rating = $publication->author->rating + 1;
        if ( $publication->author->save() ) {
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

        $publication->author->rating = $publication->author->rating - 1;
        if ( $publication->author->save() ) {
            return $publication->author_id;
        }

        return false;
    }
} 