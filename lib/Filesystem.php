<?php

class FileNotFoundException extends \Exception
{
}

class Filesystem
{
    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public static function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     */
    public static function get($path)
    {
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exist at path {$path}");
    }

    /**
     * Get the returned value of a file.
     *
     * @param  string  $path
     * @return mixed
     */
    public static function getRequire($path)
    {
        if (file_exists($path)) {
            return require $path;
        }

        throw new FileNotFoundException("File does not exist at path {$path}");
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @return int
     */
    public static function put($path, $contents)
    {
        return file_put_contents($path, $contents, LOCK_EX);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public static function append($path, $data)
    {
        return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string  $path
     * @return bool
     */
    public static function delete($path)
    {
        return unlink($path);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return void
     */
    public static function move($path, $target)
    {
        if (self::isDirectory($path)) {
            @mkdir($target);
            $directory = new \DirectoryIterator($path);
            foreach ($directory as $readdirectory) {
                if ($readdirectory->isDot() || $readdirectory->getFilename() == '.DS_Store') {
                    continue;
                }
                $pathDir = $path . '/' . $readdirectory->getFilename();
                if (self::isDirectory($pathDir)) {
                    self::copy($pathDir, $target . '/' . $readdirectory->getFilename());
                    continue;
                }

                self::copy($pathDir, $target . '/' . $readdirectory->getFilename());
            }

        } else {
            rename($path, $target);
        }
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $path
     * @param  string  $target
     * @return void
     */

    public static function copy($path, $target)
    {
        if (self::isDirectory($path)) {
            self::mkdir($target);
            $directory = new \DirectoryIterator($path);
            foreach ($directory as $readdirectory) {
                if ($readdirectory->isDot() || $readdirectory->getFilename() == '.DS_Store') {
                    continue;
                }
                $pathDir = $path . '/' . $readdirectory->getFilename();
                if (self::isDirectory($pathDir)) {
                    self::copy($pathDir, $target . '/' . $readdirectory->getFilename());
                    continue;
                }

                self::copy($pathDir, $target . '/' . $readdirectory->getFilename());
            }

        } else {
            copy($path, $target);
        }
    }

    public static function scan($directory, &$filenames = [])
    {
        $iterator = new \DirectoryIterator($directory);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || $fileinfo->getFilename() == '.DS_Store') {
                continue;
            }
            if ($fileinfo->isFile()) {
                $filenames[] = $fileinfo->getPathname();
            } elseif ($fileinfo->isDir()) {
                self::scan($directory . '/' . $fileinfo->getFilename(), $filenames);
            }
        }
        return $filenames;
    }

    public static function replaceExtension($filename, $extension)
    {
        return preg_replace('/\..+$/', '.' . $extension, $filename);
    }

    public static function mkdir($path)
    {
        return @mkdir($path, 0777, true);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Extract the file name from a file path.
     *
     * @param  string  $path
     * @return string
     */
    public static function filename($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string  $path
     * @return string
     */
    public static function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public static function size($path)
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public static function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public static function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is a real file.
     *
     * @param  string  $filename
     * @return bool
     */
    public static function isFile($filename)
    {
        return is_file($filename);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string  $directory
     * @return array
     */
    public static function files($directory)
    {
        $glob = glob($directory.'/*');

        if ($glob === false) {
            return array();
        }

        // To get the appropriate files, we'll simply glob the direectory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter(
            $glob,
            function($file) {
                return filetype($file) == 'file';
            }
        );
    }

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param  string  $directory
     * @param  bool    $preserve
     * @return void
     */
    public static function deleteDirectory($directory, $preserve = false)
    {
        if (!self::isDirectory($directory)) {
            return;
        }

        $items = new \FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-director, otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir()) {
                self::deleteDirectory($item->getRealPath());
            } else {
                self::delete($item->getRealPath());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param  string  $directory
     * @return void
     */
    public static function cleanDirectory($directory)
    {
        return self::deleteDirectory($directory, true);
    }
}

