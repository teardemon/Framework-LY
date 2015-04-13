<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-09-26
 */

namespace OBJ;


class apiResponse extends _base
{
    private $headers = [
        'Cache-control: no-cache'
    ];

    private $status = false;

    private $code = 0;

    private $message = '';

    private $data;

    /**
     * make response
     */
    public function done()
    {
        $params = ['status', 'code', 'message', 'data'];
        $res = [];
        foreach ($params as $param) {
            $res[$param] = $this->$param;
        }
        foreach ($this->headers as $header) {
            header($header);
        }
        echo json_encode($res);
    }

    /**
     * @param string|array $header
     */
    public function setHeader($header)
    {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } else {
            $this->headers[] = $header;
        }
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $data
     */
    public function success($data)
    {
        $this->setStatus(true);
        $this->setCode(1);
        $this->setMessage('success');
        $this->setData($data);
    }

    /**
     * @param $message
     * @param int $code
     */
    public function failed($message, $code=-1)
    {
        $this->setCode($code);
        $this->setMessage($message);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->$key = $value;
    }

}
