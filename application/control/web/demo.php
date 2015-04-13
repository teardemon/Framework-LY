<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-08
 */

namespace CTL\web;


use TRA\traAPI;

class demo extends _base
{
    use traAPI;

    public function index()
    {
        \view::tpl('_header');
        \view::ng('web/demo',[]);
        \view::tpl('_footer');
        \view::debug(0)->autoRefresh([
            '/resource/ng/tpl/widget/navbar.html',
            '/resource/ng/ctl/demo.js',
            '/resource/ng/src/core.js',
            '/resource/ng/src/web.js',
            '/resource/css/bootstrap-custom.css',
        ]);
    }


    public function test()
    {
        echo 'This is demo-test';
    }

    public function template()
    {
        $data = [
            'message' => "This is view Template."
        ];

        $object = new \stdClass();
        $object->message = "This is message from object.";
        \view::tpl('demo')->with('data', $data)->with('object', $object);
    }

    protected function _apiFoo()
    {
        $objRes = $this->createApiResponse();
        $objRes->success('from api-foo');
        $objRes->done();
    }
}