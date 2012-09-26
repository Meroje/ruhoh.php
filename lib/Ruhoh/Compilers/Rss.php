<?php namespace Ruhoh\Compilers;

use \Ruhoh;
use \Filesystem;
use \Ruhoh\DB;
use \Ruhoh\Page;
use \Ruhoh\Friend;

/**
 * Ruhoh rss compiler
 *
 * @todo This renders the page content even though we already need to
 *       render the content to save to disk. This will be a problem when
 *       posts numbers expand. Merge this in later.
 * @package Ruhoh\Compilers
 * @author Jerome Foray
 **/
class Rss implements ICompiler
{

    /**
     * Run this compiler
     *
     * @return void
     **/
    public static function run($target, Page $page)
    {
    }
}

