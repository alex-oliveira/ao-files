<?php

namespace AoFiles\Utils;

class FileHelper
{

    // FILE //

    public static function makeLabel($name)
    {
        return preg_replace('/(.*)\\.[^\\.]*/', '$1', $name);
    }

    public static function makeName($label, $extension, $separator = '_')
    {
        $slug = str_slug($label, $separator);
        return substr(uniqid($slug), -13) . $separator . $slug . '.' . $extension;
    }

    public static function makePrefix($id, $name)
    {
        return str_pad($id, 19, '0', STR_PAD_LEFT) . '_' . $name;
    }

    // FOLDER //

    public static function makeFolder($folders, $replaces)
    {
        return self::getPath(self::getParams($folders, $replaces));
    }

    protected static function getParams($folders, $replaces)
    {
        foreach ($folders as $child => $key)
            $folders[$child] = $replaces[$key];

        return $folders;
    }

    protected static function getPath($data)
    {
        $string = '';
        foreach ($data as $key => $item)
            $string .= '/' . $key . '/' . $item;

        return $string;
    }

}