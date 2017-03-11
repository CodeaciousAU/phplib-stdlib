<?php
/**
 * @author Glenn Schmidt <glenn@codeacious.com>
 */

namespace Codeacious\Stdlib;

/**
 * Utility for finding values in multi-dimensional arrays.
 */
abstract class ArrayTool
{
    /**
     * @param array $source The source array
     * @param string $keyPath Use colons to separate subkeys
     * @param mixed $default The value to return if the path does not exist
     * @return mixed
     */
    public static function getValueAtPath(array $source, $keyPath, $default=null)
    {
        foreach (explode(':', $keyPath) as $key)
        {
            if (!is_array($source) || !array_key_exists($key, $source))
                return $default;
            $source = $source[$key];
        }
        return $source;
    }

    /**
     * @param array $source The source array
     * @param string $keyPath Use colons to separate subkeys
     * @return array The array at $path, or an empty array
     */
    public static function getArrayAtPath(array $source, $keyPath)
    {
        $value = self::getValueAtPath($source, $keyPath);
        if (!is_array($value))
            return [];
        return $value;
    }
}