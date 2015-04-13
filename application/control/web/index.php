<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-29
 */

namespace CTL\web;


class index extends _base
{
    public function index()
    {
        echo "Welcome! This is index-index of Web.".LF;
    }

    public function env()
    {
        echo "Running environment: ".SYS_ENV;
    }

} 