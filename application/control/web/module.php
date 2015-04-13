<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-11-05
 */

namespace CTL\web;


class module extends _base
{
    public function datepicker()
    {
        \view::tpl('_header');
        \view::ng('web/datepicker',[]);
        \view::tpl('_footer');
        \view::debug(1)->autoRefresh([
            '/resource/ng/tpl/web/datepicker.html',
            '/resource/ng/src/widget.js',
            '/resource/ng/tpl/widget/datepicker.html'
        ]);
    }

} 