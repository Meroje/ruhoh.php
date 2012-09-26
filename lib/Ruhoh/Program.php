<?php namespace Ruhoh;

/**
 * Ruhoh program class, responsible of compiling and previewing applications
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Program
{

    /**
     * A program for compiling to a static website.
     * The compile environment should always be 'production' in order
     * to properly omit drafts and other development-only settings.
     *
     * @return void
     **/
    public static function compile($target)
    {
        $ruhoh = \Ruhoh::getInstance();
        $ruhoh->setup();
        $ruhoh->config['env'] = 'production';
        //DB::updateAll();
        Compiler::compile($target);
    }
}

