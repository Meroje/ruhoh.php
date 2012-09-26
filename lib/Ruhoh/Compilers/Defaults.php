<?php namespace Ruhoh\Compilers;

use \Ruhoh;
use \Filesystem;
use \Ruhoh\DB;
use \Ruhoh\Page;
use \Ruhoh\Utils;
use \Ruhoh\Friend;

/**
 * Ruhoh defaults compiler
 *
 * @package Ruhoh\Compilers
 * @author Jerome Foray
 **/
class Defaults implements ICompiler
{
    /**
     * Run this compiler
     *
     * @return void
     **/
    public static function run($target, Page $page)
    {
        self::pages($target, $page);
        self::media($target, $page);
    }

    /**
     * Compile pages
     *
     * @return void
     **/
    public static function pages($target, Page $page)
    {
        foreach (DB::allPages() as $p) {
            $page->change($p['id']);

            Filesystem::mkdir(dirname($page->compiled_path));
            Filesystem::put($page->compiled_path, $page->render());

            Friend::say(
                function($f) use($p) {
                    $f::green('processed: ' . $p['id']);
                }
            );
        }
    }

    /**
     * Compile media
     *
     * @return void
     **/
    public static function media($target, Page $page)
    {
        $path = Ruhoh::getInstance()->paths['media'];
        if (!Filesystem::isDirectory($path)) {
            return;
        }
        $media = Utils::urlToPath($path, $target);
        Filesystem::mkdir($media);
        Filesystem::copy($path, $media);
    }
}

