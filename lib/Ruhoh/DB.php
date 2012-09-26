<?php namespace Ruhoh;

use \Ruhoh;
use \Filesystem;
use Compilers\Theme;
use Compilers\Rss;

/**
 * Ruhoh DB class, creates the static website
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class DB
{
    /**
     * Site
     *
     * @var array
     **/
    public static $site = [];

    /**
     * Posts
     *
     * @var array
     **/
    public static $posts = [];

    /**
     * Pages
     *
     * @var array
     **/
    public static $pages = [];

    /**
     * Routes
     *
     * @var array
     **/
    public static $routes = [];

    /**
     * Partials
     *
     * @var array
     **/
    public static $partials = [];

    /**
     * Widgets
     *
     * @var array
     **/
    public static $widgets = [];

    /**
     * Stylesheets
     *
     * @var array
     **/
    public static $stylesheets = [];

    /**
     * Javascripts
     *
     * @var array
     **/
    public static $javascripts = [];

    /**
     * Payload
     *
     * @var array
     **/
    public static $payload = [];

    /**
     * Update the given type
     *
     * @param string $type
     * @return void
     **/
    public static function update($type = null)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'site':
                Parsers\Site::generate();
                break;
            case 'routes':
                Parsers\Routes::generate();
                break;
            case 'posts':
                Parsers\Posts::generate();
                break;
            case 'pages':
                Parsers\Pages::generate();
                break;
            case 'layouts':
                Parsers\Layouts::generate();
                break;
            case 'partials':
                Parsers\Partials::generate();
                break;
            case 'widgets':
                Parsers\Widgets::generate();
                break;
            case 'stylesheets':
                Parsers\Stylesheets::generate();
                break;
            case 'javascripts':
                Parsers\Javascripts::generate();
                break;
            case 'payload':
                Parsers\Payload::generate();
                break;
            default:
                throw new \InvalidArgumentException("Data type '$type' is not a valid data type.");
                break;
        }
    }

    /**
     * Always regenerate a fresh payload since it
     * references other generated data.
     *
     * @return array
     **/
    public static function payload()
    {
        self::update('payload');
        return self::$payload;
    }

    /**
     * Fetch pages
     *
     * @return array
     **/
    public static function allPages()
    {
        self::updateAll();
        return array_merge(self::$posts['dictionary'], self::$pages);
    }

    /**
     * Updates all types at once
     *
     * @return array
     **/
    public static function updateAll()
    {
        foreach (get_class_vars(__CLASS__) as $type => $value) {
            self::update($type);
        }
    }
}

