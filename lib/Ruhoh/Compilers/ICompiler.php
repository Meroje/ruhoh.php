<?php namespace Ruhoh\Compilers;

use \Ruhoh\Page;

/**
 * Ruhoh compiler interface
 *
 * @package Ruhoh\Compilers
 * @author Jerome Foray
 **/
interface ICOmpiler
{
    /**
     * Run this compiler
     *
     * @param  string  $target
     * @param  Page    $page
     * @return void
     */
    public static function run($target, Page $page);
}

