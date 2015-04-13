<html>
    <head>
<?php
    view::js('config');
    view::js('header');
    view::css('common');
    view::js('common');
    view::js('/lib/jquery/jquery', true);
    view::tpl('_srcAngular');
    view::tpl('_srcBootstrap');
    if (SYS_ENV == ENV_DEV) {
        view::js('develop');
    }
?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body ng-app="web" class="ng-clock">
