<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


interface BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool
     */
    public static function plus($postId, \User $author);

    /**
     * @param $postId
     * @param \User $author
     * @return mixed
     */
    public static function minus($postId, \User $author);
} 