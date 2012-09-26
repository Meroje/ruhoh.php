<?php namespace Ruhoh;

/**
 * Structured container for all paths to relevant directories and files in the system.
 * Paths are based on the ruhohspec for the Universal Blog API.
 * Additionally we store some system (gem) level paths for cascading to default functionality,
 * such as default widgets, default dashboard view, etc.
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Paths
{

    /**
    * Config
    *
    * @var array
    **/
    protected static $paths = array(
        'base' => '',
        'compiled' => '',
        'config_data' => '',
        'dashboard_file' => '',
        'media' => '',
        'pages' => '',
        'partials' => '',
        'plugins' => '',
        'posts' => '',
        'site_data' => '',
        'themes' => '',
        'widgets' => '',

        'theme' => '',
        'theme_config_data' => '',
        'theme_dashboard_file' => '',
        'theme_layouts' => '',
        'theme_media' => '',
        'theme_partials' => '',
        'theme_javascripts' => '',
        'theme_stylesheets' => '',
        'theme_widgets' => '',

        'system_dashboard_file' => '',
        'system_widgets' => ''
    );

    /**
     * Generate paths array from config
     *
     * @return array or false on failure
     **/
    public static function generate($config, $base)
    {
        $ruhoh = \Ruhoh::getInstance();
        defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);

        self::$paths['base']           = $base;
        self::$paths['config_data']       = realpath($base . DS . $ruhoh->names['config_data']);
        self::$paths['pages']    = realpath($base . DS . $ruhoh->names['pages']);
        self::$paths['posts'] = realpath($base . DS . $ruhoh->names['posts']);
        self::$paths['partials']          = realpath($base . DS . $ruhoh->names['partials']);
        self::$paths['media']          = realpath($base . DS . $ruhoh->names['media']);
        self::$paths['widgets']       = realpath($base . DS . $ruhoh->names['widgets']);
        self::$paths['compiled']        = realpath($base . DS . $ruhoh->names['compiled']);
        self::$paths['dashboard_file']          = realpath($base . DS . $ruhoh->names['dashboard_file']);
        self::$paths['site_data']      = realpath($base . DS . $ruhoh->names['site_data']);
        self::$paths['themes']         = realpath($base . DS . $ruhoh->names['themes']);
        self::$paths['plugins']        = realpath($base . DS . $ruhoh->names['plugins']);

        self::$paths['theme']          = realpath($base . DS . $ruhoh->names['themes'] . DS . $config['theme']);
        self::$paths['theme_config_data'] = realpath(self::$paths['theme'] . DS . $ruhoh->names['dashboard_file']);
        self::$paths['theme_dashboard_file'] = realpath(self::$paths['theme'] . DS . $ruhoh->names['theme_config']);
        self::$paths['theme_layouts']  = realpath(self::$paths['theme'] . DS . $ruhoh->names['layouts']);
        self::$paths['theme_media']    = realpath(self::$paths['theme'] . DS . $ruhoh->names['stylesheets']);
        self::$paths['theme_partials'] = realpath(self::$paths['theme'] . DS . $ruhoh->names['javascripts']);
        self::$paths['theme_javascripts'] = realpath(self::$paths['theme'] . DS . $ruhoh->names['media']);
        self::$paths['theme_stylesheets'] = realpath(self::$paths['theme'] . DS . $ruhoh->names['widgets']);
        self::$paths['theme_widgets']  = realpath(self::$paths['theme'] . DS . $ruhoh->names['partials']);

        if (!self::themeIsValid(self::$paths)) {
            return false;
        }

        self::$paths['system_dashboard_file'] = realpath($ruhoh->root . DS . $ruhoh->names['dashboard_file']);
        self::$paths['system_widgets']        = realpath($ruhoh->root . DS . $ruhoh->names['widgets']);

        return self::$paths;
    }

    /**
     * Check theme validity
     *
     * @return bool
     **/
    public static function themeIsValid($paths)
    {
        if (\Filesystem::isDirectory($paths['theme'])) {
            return true;
        }
        \Ruhoh::getInstance()->log->error("Theme directory does not exist: " . $paths['theme']);
        return false;
    }
}

