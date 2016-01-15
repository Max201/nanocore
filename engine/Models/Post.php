<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Post
 * @package Model
 */
class Post extends Model
{
    static $before_create = ['created_at'];
    static $after_create = ['export'];
    static $before_save = ['updated_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['category', 'class_name' => 'PostCategory', 'foreign_key' => 'category_id'],
    );

    /**
     * Defines create date
     */
    public function created_at()
    {
        $this->assign_attribute('created_at', time());
    }

    /**
     * Defines update date
     */
    public function updated_at()
    {
        $this->assign_attribute('updated_at', time());
    }

    /**
     * @param null $base
     * @return array
     */
    public function images($base = null)
    {
        if ( is_null($base) ) {
            $base = \System\Environment\Env::$request->getSchemeAndHttpHost();
        }

        // Search images
        preg_match_all('#<img.+?src="(.+?)"#', $this->content, $m);
        $images = [];
        if ( $m ) {
            foreach ( $m[1] as $img ) {
                if ( strpos($img, '../../') > -1 ) {
                    $img = substr($img, 5);
                }

                if ( $img[0] == '/' ) {
                    $img = $base . $img;
                }

                $images[] = $img;
            }
        }

        return $images;
    }

    /**
     * @param int $max_len
     * @return string
     */
    public function content_plain($max_len=-1)
    {
        $content = $max_len > 0 ? substr($this->content, 0, $max_len) : $this->content;
        return strtr($content, [
            '</div>'    => "\n",
            '</p>'      => "\n\n",
            '<br/>'     => "\n",
            "&nbsp;"    => " "
        ]);
    }

    /**
     * @return array
     */
    public function hashtags()
    {
        $tags = explode(' ', $this->keywords);
        return array_map(function($i){ return '#' . $i; }, $tags);
    }

    /**
     * Exporting to social
     */
    public function export()
    {
        /** @var \Service\SocialMedia\SocialMedia $smp */
        $smp = \System\Engine\NCService::load('SocialMedia');
        $post_url = '/post/' . $this->id . '-' . $this->slug . '.html';
        $url = \System\Environment\Env::$request->getSchemeAndHttpHost() . $post_url;

        // Posting vk
        if ( $this->category->post_vkontakte ) {
            $vk = $smp->vk();
            $this->assign_attribute(
                'post_vkontakte',
                $vkp = $vk->m_post(
                    $this->category->post_vkontakte,
                    $this->title . "\n\n" .
                    $this->content_plain() .
                    implode(' ', $this->hashtags()),
                    [
                        'attachments'   => $url
                    ]
                )
            );
        }

        // Posting twitter
        if ( $this->category->post_twitter ) {
            $tw = $smp->tw();
            $this->assign_attribute(
                'post_twitter',
                $tw->m_post(
                    $url . ' ' . $this->title . ' ' . implode(' ', $this->hashtags()),
                    reset($this->images(ROOT))
                )->id
            );
        }
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author'    => $this->author->to_array(),
            'category'  => $this->category->to_array()
        ], parent::to_array());
    }
}