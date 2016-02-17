<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Visit
 * @package Model
 */
class Visit extends Model
{
    /**
     * Selects Popular pages
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $limit
     * @return array
     */
    public static function pages($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(page) AS uri,
                (SELECT COUNT(id) FROM visits WHERE page = uri) AS views
            FROM visits WHERE
                page IS NOT NULL
                AND `time` > ?
                AND `time` < ?
            ORDER BY views
            DESC LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Selects popular search query terms
     *
     * @param int $start_date
     * @param int $end_date
     * @param int $limit
     * @return array
     */
    public static function query_terms($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(search) AS term,
                (SELECT COUNT(id) FROM visits WHERE search = term) AS visits
            FROM visits WHERE
                search IS NOT NULL
                AND internal = 0
                AND `time` > ?
                AND `time` < ?
            ORDER BY visits
            DESC LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Online users counter
     *
     * @return int
     */
    public static function online()
    {
        $users = static::find_by_sql('SELECT
                COUNT(DISTINCT(ip)) AS online
            FROM visits
            WHERE `time` > ?', [time() - 300]);
        return reset($users)->online;
    }

    /**
     * Selects popular browsers
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     * @return array
     */
    public static function browsers($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(browser) AS browser_name,
                (SELECT COUNT(id) FROM visits WHERE browser = browser_name) AS views
            FROM visits WHERE
                `time` > ?
                AND `time` < ?
            ORDER BY views DESC
            LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Selects popular browsers
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     * @return array
     */
    public static function platforms($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(platform) AS platform_name,
                (SELECT COUNT(id) FROM visits WHERE platform = platform_name) AS views
            FROM visits WHERE
                platform IS NOT NULL
                AND `time` > ?
                AND `time` < ?
            ORDER BY views
            DESC LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Gets a popular external websites
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     * @return array
     */
    public static function websites($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(`domain`) AS website,
                (SELECT COUNT(id) FROM visits WHERE `domain` = website) AS visits
            FROM visits WHERE
                internal = 0
                AND `time` > ?
                AND `time` < ?
                AND `domain` IS NOT NULL
            ORDER BY visits
            DESC LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Count unique visits
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     * @return array
     */
    public static function visitors($start_date, $end_date, $limit = 10)
    {
        $visits = static::find_by_sql('SELECT
                DISTINCT(ip) AS long_ip,
                (SELECT COUNT(id) FROM visits WHERE ip = long_ip) AS views
            FROM visits WHERE
                `time` > ?
                AND `time` < ?
            ORDER BY views
            DESC LIMIT ' . intval($limit), [$start_date, $end_date]);

        return static::as_array($visits);
    }

    /**
     * Count unique visits
     *
     * @param $start_date
     * @param $end_date
     * @return int
     */
    public static function visits($start_date, $end_date)
    {
        $visits = static::find_by_sql('SELECT
                COUNT(DISTINCT(ip)) AS visits
            FROM visits WHERE
                `time` > ?
                AND `time` < ?', [$start_date, $end_date]);

        return reset($visits)->visits;
    }

    /**
     * Count unique visits
     *
     * @param $start_date
     * @param $end_date
     * @return int
     */
    public static function views($start_date, $end_date)
    {
        $visits = static::find_by_sql('SELECT
                COUNT(DISTINCT(page)) AS views
            FROM visits WHERE
                `time` > ?
                AND `time` < ?', [$start_date, $end_date]);

        return reset($visits)->views;
    }

    /**
     * @param User $user
     * @return array
     */
    public static function ips_by_user(\User $user)
    {
        $visits = static::find_by_sql('SELECT DISTINCT(ip) AS ip FROM visits WHERE `user_id` = ?', [$user->id]);
        return $visits;
    }
}