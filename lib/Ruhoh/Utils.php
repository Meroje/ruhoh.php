<?php namespace Ruhoh;

use \Ruhoh;
use \Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Ruhoh utils class
 *
 * @package Ruhoh
 * @author Jerome Foray
 **/
class Utils
{

    /**
     * Translate to application relative path
     *
     * @return string
     **/
    public static function relativePath($filename)
    {
        return str_replace(Ruhoh::getInstance()->paths['base'] . "/", '', $filename);
    }

    /**
     * Translate to application relative path
     *
     * @return array
     **/
    public static function parsePageFile()
    {
        $path = implode('/', func_get_args());
        if (!Filesystem::exists($path)) {
            throw \FileNotFoundException("File not found: $path");
        }

        $page = Filesystem::get($path);

        $split = preg_split("/[\n]*[-]{3}[\n]/", $page, 3, PREG_SPLIT_NO_EMPTY);
        if (!empty($split)) {
            $split[0] = preg_split("/((\r(?!\n))|((?<!\r)\n)|(\r\n))/", $split[0]);
            // Strip extra, non-indentation, whitespace from beginning of lines
            $front_matter = "";
            foreach ($split[0] as $line) {
                $line = trim($line);
                $front_matter .= $line . "\n";
            }
            try {
                $data = Yaml::parse(str_replace('---', '', $front_matter));
            } catch (ParseException $e) {
                $data = [];
            }
            $data['categories'] = isset($data['categories']) ? $data['categories'] : [];
            $data['tags'] = isset($data['tags']) ? $data['tags'] : [];
            return ['data' => $data, 'content' => isset($split[1]) ? $split[1] : ''];
        } else {
            return ['data' => '', 'content' => ''];
        }
    }
}

