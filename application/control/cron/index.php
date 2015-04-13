<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-09-23
 */

namespace CTL\Cron;


class index extends _base
{
    public function index()
    {
        echo "Welcome! This is index-index of Cron." . LF;
    }

    public function showArgu($name)
    {
        echo \input::cli($name)->value();
    }

} 