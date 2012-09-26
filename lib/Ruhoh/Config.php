<?php namespace Ruhoh;

use \Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Yaml\Exception\ParseException;

/**
 * Structured container for global configuration parameters.
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Config
{

    /**
    * Config
    *
    * @var array
    **/
    protected static $config = array(
        'env' => null, // Ruhoh env
        'pages_exclude' => array(), // Pages to be excluded
        'pages_permalink' => null, // Base permalink for pages
        'pages_layout' => 'page', // Default pages layout
        'posts_exclude' => array(), // Posts to be excluded
        'posts_layout' => 'post', // Default posts layout
        'posts_permalink' => null, // Base permalink for posts
        'rss_limit' => 20, // Maximum number of items in rss feed
        'theme' => '', // Application theme
    );

    /**
     * Get config from file
     *
     * @return array or false on failure
     **/
    public static function generate($path_to_config)
    {
        $ruhoh = \Ruhoh::getInstance();
        try {
            $site_config = Yaml::parse($path_to_config);
        } catch (ParseException $e) {
            $ruhoh->log->error(
                "Empty site_config\nEnsure ./" .
                \Ruhoh::getInstance()->names['config_data'] .
                " exists and contains valid YAML"
            );
            return false;
        }

        $theme = isset($site_config['theme']) ? strtolower($site_config['theme']) : '';
        if (empty($theme)) {
            $ruhoh->log->error("Theme not specified in " . $ruhoh->names['config_data']);
            return false;
        }

        self::$config['theme'] = $theme;
        if (isset($site_config['env'])) {
            self::$config['env'] = $site_config['env'];
        }

        if (isset($site_config['rss']['limit'])) {
            self::$config['rss_limit'] = $site_config['rss']['limit'];
        }

        if (isset($site_config['posts']['permalink'])) {
            self::$config['posts_permalink'] = $site_config['posts']['permalink'];
        }
        if (isset($site_config['posts']['layout'])) {
            self::$config['posts_layout'] = $site_config['posts']['layout'];
        }
        if (isset($site_config['posts']['exclude'])) {
            self::$config['posts_exclude'] = (array) $site_config['posts']['exclude'];
        }

        if (isset($site_config['pages']['permalink'])) {
            self::$config['pages_permalink'] = $site_config['pages']['permalink'];
        }
        if (isset($site_config['pages']['layout'])) {
            self::$config['pages_layout'] = $site_config['pages']['layout'];
        }
        if (isset($site_config['pages']['exclude'])) {
            self::$config['pages_exclude'] = (array) $site_config['pages']['exclude'];
        }

        return self::$config;
    }
}

