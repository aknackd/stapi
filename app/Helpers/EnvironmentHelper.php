<?php

namespace App\Helpers;

use App\Exceptions\EnvironmentException;

class EnvironmentHelper
{
    /**
     * Returns whether or not we're on a Windows machine.
     *
     * @return boolean True or false
     */
    public static function isWindows()
    {
        return preg_match('/^win/i', PHP_OS);
    }

    /**
     * Returns whether or not we're on a *nix machine.
     *
     * @return boolean True or false
     */
    public static function isNix()
    {
        return preg_match('/(^Linux|BSD$)/i', PHP_OS);
    }

    /**
     * Returns whether or not we're on a MacOS machine.
     *
     * @return boolean True or false
     */
    public static function isMacOS()
    {
        return PHP_OS == 'Darwin';
    }

    /**
     * Returns the current user's home directory.
     *
     * @return string Path to current user's home directory
     * @throws \App\Exceptions\EnvironmentException If running on an unsupported OS
     */
    public static function getUserHomeDirectory()
    {
        if (self::isNix() || self::isMacOS()) {
            $homeDir = rtrim(env('HOME'), '/');
        } else if (self::isWindows()) {
            $homeDir = trim($_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'], '\\/');
        } else {
            throw new EnvironmentException(
                'Could not find user\'s home directory: unsupported OS');
        }

        return $homeDir;
    }

    /**
     * Returns the current user's cache directory.
     *
     * @param string $subdir Subdirectory
     * @return string Path to current user's cache directory
     */
    public static function getUserCacheDirectory($subdir = null)
    {
        $homeDir = self::getUserHomeDirectory();

        if (self::isWindows()) {
            $cacheDir = env('LOCALAPPDATA');
        } else if (self::isMacOS()) {
            $cacheDir = $homeDir.'/Library/Caches';
        } else if (self::isNix()) {
            $cacheDir = $homeDir.'/.cache';
        }

        if ($subdir !== null) {
            $cacheDir .= '/'.$subdir;
        }

        return $cacheDir;
    }

    /**
     * Finds the full path of an executable in $PATH.
     *
     * @param string $filename Filename
     * @return string Full path to binary file
     * @throws App\Exceptions\EnvironmentException If file cannot be found in $PATH
     */
    public static function findInPath($filename)
    {
        foreach (explode(PATH_SEPARATOR, env('PATH')) as $dir) {
            $path = $dir.DIRECTORY_SEPARATOR.$filename;
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }
        throw new EnvironmentException(
            sprintf('Could not find "%s" in $PATH', $filename));
    }
}
