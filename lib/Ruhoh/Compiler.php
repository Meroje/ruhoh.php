<?php namespace Ruhoh;

use \Ruhoh;
use \Filesystem;
use Compilers\Theme;
use Compilers\Rss;

/**
 * Ruhoh compiler class, creates the static website
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Compiler
{

    /**
     * Compile the application
     *
     * @todo seems rather dangerous to delete the incoming target directory?
     * @param string $target_directory
     * @param Page $page page model
     * @return void
     **/
    public static function compile($target_directory = null, Page $page = null)
    {
        $ruhoh = Ruhoh::getInstance();
        Friend::say(
            function($f) use($ruhoh) {
                $env = $ruhoh->config['env'];
                $f::plain("Compiling for environment: '$env'");
            }
        );
        $target_directory = ($target_directory) ?: './' . $ruhoh->names['compiled'];
        $page = ($page) ?: new Page();

        if (Filesystem::exists($target_directory)) {
            if (Filesystem::isDirectory($target_directory)) {
                Filesystem::deleteDirectory($target_directory);
            } else {
                Filesystem::delete($target_directory);
            }
        }
        Filesystem::mkdir($target_directory);

        $mask = '{' . $ruhoh->paths['plugins'] . '/compiler-tasks/*.php,' . $ruhoh->getRoot() . '/lib/Ruhoh/Compilers/*.php}';
        foreach (glob($mask, GLOB_BRACE) as $compiler) {
            $class = '\\Ruhoh\\Compilers\\' . Filesystem::filename($compiler);
            if (!class_exists($class, false)) {
                require_once $compiler;
                if (method_exists($class, 'run') && !interface_exists($class) && !interface_exists($class)) {
                    call_user_func([$class, 'run'], $target_directory, $page);
                }
            }
        }
    }
}

