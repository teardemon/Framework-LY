<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-26
 */

class router
{
    public static function locate($prefix=null)
    {
        $path = parse_url($_SERVER['REQUEST_URI'])['path'];
        $locator = $prefix ? "CTL/$prefix/$path" : $path;
        return $locator;
    }

    public static function locateFromReferer($prefix=null)
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            return false;
        }
        $path = parse_url($_SERVER['HTTP_REFERER'])['path'];
        $locator = $prefix ? "CTL/$prefix/$path" : $path;
        return $locator;
    }

    public static function parseLocator($locator)
    {
        $location = preg_replace('#/+#','/', $locator);
        $flags = explode('/', $location);
        $focus = array_pop($flags);
        $parts = explode('-', $focus);
        $flags[]  = empty ($parts[0]) ? 'index' : $parts[0];// 1st for controller
        $method = empty ($parts[1]) ? 'index' : $parts[1];// 2nd for method
        $params = array_slice($parts, 2);// rest for parameters
        $control= implode('\\', $flags);
        $ret = [
            'control' => $control,
            'method' => $method,
            'params' => $params,
        ];
        return $ret;
    }
}