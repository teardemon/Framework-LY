<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-14
 */

require __DIR__.'/../boot/bootstrap.php';

$option = $argv[1];

if (is_callable('init', $option)) {
    init::$option();
} else {
    echo "Illegal init mode.\n";
}

class init
{
    public static function config()
    {
        $cnt = config::init();
        echo "$cnt config files parsed.\n";
    }

    public static function database()
    {

    }
}

