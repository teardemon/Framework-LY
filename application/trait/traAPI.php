<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-09-05
 */

namespace TRA;

use OBJ\apiResponse;

trait traAPI
{
    public function api($action)
    {
        $method = sprintf('_api%s', ucfirst($action));
        if (method_exists($this,$method)) {
            $this->$method();
        } else {
            $response = [
                'status' => false,
                'message' => "Cannot find api method `$action`",
                'data' => [],
            ];
            $res = $this->createApiResponse($response);
            $res->done();
        }
    }

    protected function createApiResponse(array $infos=null)
    {
        $obj = new apiResponse();
        if ($infos !== null) {
            foreach ($infos as $key => $val) {
                $obj->set($key, $val);
            }
        }
        return $obj;
    }

}
