<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-19
 */

namespace CTL\web;

class test extends _base
{
    public function index()
    {
        print_r($_SERVER);
    }

    public function ng()
    {
        \view::tpl('_header');
        \view::ng('web/test', []);
        \view::tpl('_footer');
    }

    public function stream($method)
    {
        $res = \phpStream::$method();
        var_dump ($res);
    }


}