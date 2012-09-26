<?php namespace Ruhoh;

/**
 * Structured container for all pre-defined URLs in the system.
 * These URLs are used primarily for static assets in development mode.
 * When compiling, all urls are of course mapped literally to the asset filepaths.
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Urls
{

    /**
    * Config
    *
    * @var array
    **/
    protected static $urls = array(
        'media' => '',
        'widgets' => '',
        'dashboard' => '',
        'theme' => '',
        'theme_media' => '',
        'theme_javascripts' => '',
        'theme_stylesheets' => '',
        'theme_widgets' => ''
    );

    /**
     * Generate urls config
     *
     * @return array or false on failure
     **/
    public static function generate($config)
    {
        $ruhoh = \Ruhoh::getInstance();
        defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);

        self::$urls['media']             = self::toUrl($ruhoh->names['assets'], $ruhoh->names['media']);
        self::$urls['widgets']           = self::toUrl($ruhoh->names['assets'], $ruhoh->names['widgets']);
        self::$urls['dashboard']         = self::toUrl(explode('.', $ruhoh->names['dashboard_file'])[0]);

        self::$urls['theme']             = self::toUrl($ruhoh->names['assets'], $config['theme']);
        self::$urls['theme_media']       = self::toUrl($ruhoh->names['assets'], $config['theme'], $ruhoh->names['media']);
        self::$urls['theme_javascripts'] = self::toUrl($ruhoh->names['assets'], $config['theme'], $ruhoh->names['javascripts']);
        self::$urls['theme_stylesheets'] = self::toUrl($ruhoh->names['assets'], $config['theme'], $ruhoh->names['stylesheets']);
        self::$urls['theme_widgets']     = self::toUrl($ruhoh->names['assets'], $config['theme'], $ruhoh->names['widgets']);

        return self::$urls;
    }

    /**
     * Translate to url
     *
     * @return string
     **/
    public static function toUrl()
    {
        return implode('/', func_get_args());
    }

    /**
     * Translate to url slug
     *
     * @return string
     **/
    public static function toUrlSlug($title)
    {
        return urlencode(self::toSlug($title));
    }

    /**
     * Translate to slug
     * My Post Title ===> my-post-title
     *
     * @return string
     * @todo Handle special chars
     **/
    public static function toSlug($title)
    {
        $title = str_replace(' ', '-', strtolower($title));
        return $title;
    }
}

