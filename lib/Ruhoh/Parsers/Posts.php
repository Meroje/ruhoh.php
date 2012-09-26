<?php namespace Ruhoh\Parsers;

use \Ruhoh;
use \Ruhoh\Urls;

/**
 * Ruhoh posts parser class
 *
 * @package Ruhoh\Parsers
 * @author Jerome Foray
 **/
class Posts
{

    /**
     * regex to check if post have date in title
     *
     * @var string
     */
    const DateMatcher = "/^(.+\/)*(\d+-\d+-\d+)-(.*)(\.[^.]+)$/";

    /**
    * regex to check if post have valid title
    *
    * @var string
    */
    const Matcher = "/^(.+\/)*(.*)(\.[^.]+)$/";

    /**
     * Format date to human readable format
     *
     * @return string
     **/
    public static function formattedDate($date = null)
    {
        $date = $date ?: time();
        return strftime('%Y-%m-%d', $date);
    }

    /**
     * Lists all posts
     *
     * @return array
     **/
    public static function files()
    {
        $files= [];
        foreach (\Filesystem::scan(\Ruhoh::getInstance()->names['posts']) as $filename) {
            if (self::isValidPage($filename)) {
                $files[] = $filename;
            }
        }
        return $files;
    }

    /**
     * Check file validity
     *
     * @return bool
     **/
    public static function isValidPage($filepath)
    {
        if (\Filesystem::isDirectory($filepath)) {
            return false;
        }
        if (substr(array_reverse(explode('/', $filepath))[0], 0, 1) == '.') {
            return false;
        }
        foreach (\Ruhoh::getInstance()->config['posts_exclude'] as $regex) {
            if (preg_match($regex, $filepath)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Posts ordered in reverse chronological order
     *
     * @param $dictionary array
     * @return array
     **/
    public static function orderedPosts($dictionary)
    {
        $ordered_posts = [];
        foreach ($dictionary as $val) {
            $ordered_posts[] = $val;
        }
        usort(
            $ordered_posts,
            function ($a, $b) {
                $date_a = new DateTime($a['date']);
                $date_b = new DateTime($b['date']);
                return ($date_b < $date_a) ? -1 : 1;
            }
        );

        return $ordered_posts;
    }

    /**
     * Get post's info from filename
     *
     * @param $filename string
     * @return array
     **/
    public static function parsePageFilename($filename)
    {
        $date_match = preg_match(self::DateMatcher, $filename, $data_date);
        $match = preg_match(self::Matcher, $filename, $data);
        if ($date_match != 1 && $match != 1) {
            return [];
        } elseif ($date_match == 1) {
            return [
                "path" => $data[1],
                "date" => $data[2],
                "slug" => $data[3],
                "title" => self::toTitle($data[3]),
                "extension" => $data[4]
            ];
        } else {
            return [
                "path" => $data[1],
                "slug" => $data[2],
                "title" => self::toTitle($data[2]),
                "extension" => $data[3]
            ];
        }
    }

    /**
     * Get title from slug
     * my-post-title ===> My Post Title
     *
     * @param $filename string
     * @return string
     **/
    public static function toTitle($file_slug)
    {
        $title = str_replace('-', ' ', strtoupper($title));
        return $title;
    }

    /**
     * Used in the client implementation to turn a draft into a post.
     *
     * @param $data array
     * @return string
     **/
    public static function toFilename($data)
    {
        $filename  = Ruhoh::getInstance()->paths['posts'] . DIRECTORY_SEPARATOR;
        $filename .= Urls::toSlug($data['title']) . '.' . $data['ext'];
        return $filename;
    }

    /**
     * Another blatently stolen method from Jekyll
     * The category is only the first one if multiple categories exist.
     *
     * @param $post array
     * @return string
     * @todo what does `File.basename` returns ?
     **/
    public static function permalink($post)
    {
        $date   = new DateTime($post['date']);
        $title  = Urls::toUrlSlug($post['title']);
        $format = $post['permalink'] ?: Ruhoh::getInstance()->config['posts_permalink'] ?: "/:categories/:year/:month/:day/:title.html";

        // Use the literal permalink if it is a non-tokenized string.
        if (!strstr($format, ':')) {
            $url = split('/', $format);
            array_walk(
                $url,
                function (&$value, $key) {
                    $value = urlencode($value);
                }
            );
            $url = '/' . implode('/', $url);
        }
        // WIP
    }
}

