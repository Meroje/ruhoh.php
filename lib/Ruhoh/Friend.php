<?php namespace Ruhoh;

/**
 * Ruhoh friend class
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Friend
{

    /**
     * Say something
     *
     * @return void
     **/
    public static function say($msg)
    {
        if (is_callable($msg)) {
            $msg(__CLASS__);
        }
    }

    /**
     * Can we say it in color ?
     *
     * @param string color
     * @return bool
     * @todo Adds ability to disable if color is not supported?
     **/
    public static function colorEnabled($color)
    {
        return true;
    }

    /**
     * Output formatted text
     *
     * @return void
     **/
    public static function color($text, $color_code)
    {
        $out = (self::colorEnabled($color_code)) ? "${color_code}${text}\e[0m" : $text;
        echo "$out\n";
    }

    public static function plain($text = "\n")
    {
        echo $text;
    }

    public static function bold($text = '')
    {
        self::color($text, "\e[1m");
    }

    public static function red($text = '')
    {
        self::color($text, "\e[31m");
    }

    public static function green($text = '')
    {
        self::color($text, "\e[32m");
    }

    public static function yellow($text = '')
    {
        self::color($text, "\e[33m");
    }

    public static function blue($text = '')
    {
        self::color($text, "\e[34m");
    }

    public static function magenta($text = '')
    {
        self::color($text, "\e[35m");
    }

    public static function cyan($text = '')
    {
        self::color($text, "\e[36m");
    }

    public static function white($text = '')
    {
        self::color($text, "\e[37m");
    }
}

