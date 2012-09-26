<?php

use Ruhoh\Logger;
use Ruhoh\Config;
use Ruhoh\Paths;
use Ruhoh\Urls;

/**
 * Ruhoh main class
 *
 * @package default
 * @author Jerome Foray
 **/
class Ruhoh
{

    use \FlorianWolters\Component\Util\Singleton\SingletonTrait;

    /**
     * Ruhoh version
     *
     * @var string
     **/
    const VERSION = '1.0.0.alpha';

    /**
     * Ruhohspec version
     *
     * @var string
     **/
    const RUHOHSPEC = '1.0';

    /**
     * Application config
     *
     * @var array
     **/
    public $config = [];

    /**
     * File names, part of ruhospec
     *
     * @var array
     **/
    public $names = [
        'assets' => 'assets',
        'config_data' => 'config.yml',
        'compiled' => 'compiled',
        'dashboard_file' => 'dash.html',
        'layouts' => 'layouts',
        'media' => 'media',
        'pages' => 'pages',
        'partials' => 'partials',
        'plugins' => 'plugins',
        'posts' => 'posts',
        'javascripts' => 'javascripts',
        'site_data' => 'site.yml',
        'stylesheets' => 'stylesheets',
        'themes' => 'themes',
        'theme_config' => 'theme.yml',
        'widgets' => 'widgets',
        'widget_config' => 'config.yml'
    ];

    /**
     * Application paths
     *
     * @var array
     **/
    public $paths = [];

    /**
     * Ruhoh base path
     *
     * @var string
     **/
    public $root = '';

    /**
     * Application urls
     *
     * @var array
     **/
    public $urls = [];

    /**
     * Application base path
     *
     * @var string
     **/
    protected $base = '';

    /**
     * Logger
     *
     * @var object
     **/
    public $log = null;

    /**
     * Setup Ruhoh utilities relative to the current application directory
     *
     * @return void
     **/
    public function setup($opts = [])
    {
        $this->root = self::getRoot();
        $this->log = new Logger();

        $this->reset();

        if (isset($opts['log_file'])) {
            $this->logger->log_file = $opts['log_file'];
        }
        if (isset($opts['source'])) {
            $this->base = $opts['source'];
        }

        $this->config = Config::generate($this->names['config_data']);
        $this->paths  = Paths::generate($this->config, $this->base);
        $this->urls   = Urls::generate($this->config);

        if (isset($opts['enable_plugins'])) {
            $this->setupPlugins();
        }
    }

    /**
     * Set application base path
     *
     * @return void
     **/
    public function reset()
    {
        $this->base = getcwd();
    }

    /**
     * Include plugins files
     *
     * @return void
     **/
    public function setupPlugins()
    {
        foreach (glob($this->paths['plugins'] . '**/*.php') as $plugin) {
            require $plugin;
        }
    }

    /**
     * Get Ruhoh ROOT path
     *
     * @return string
     **/
    public static function getRoot()
    {
        return realpath(dirname(__DIR__));
    }
}

