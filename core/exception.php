<?php

/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-02
 */
class coreException extends Exception
{

    use traException;

    /**
     * exception type
     * range [0,255]
     */
    const expType = 1;

    const UNKNOWN_EXCEPTION = 0;

    const BOOT_MODE_IS_UNDEFINED = 1;

    const AUTOLOAD_FAILED = 2;

    const CONFIG_NOT_FOUND = 3;

    const CONTROLLER_NOT_FOUND = 4;

    const METHOD_NOT_FOUND = 5;

    const TEMPLATE_NOT_FOUND = 6;


    protected $descriptions = [
        self::UNKNOWN_EXCEPTION => 'Undefined exception `%s`.',
        self::BOOT_MODE_IS_UNDEFINED => 'BOOT_MODE is undefined.',
        self::AUTOLOAD_FAILED => 'Failed loading `%s`.',
        self::CONFIG_NOT_FOUND => 'Cannot find config `%s`.',
        self::CONTROLLER_NOT_FOUND => 'Cannot find controller `%s`',
        self::METHOD_NOT_FOUND => 'Cannot find method `%s-%s`.',
        self::TEMPLATE_NOT_FOUND => 'Cannot find template `%s`.',
    ];


    public function __construct($exception, array $argus=[], $preException = null)
    {
        $this->setDescription($this->descriptions);
        $message = $this->makeMessage($exception, $argus);
        $code = $this::makeCode(self::expType, $exception);
        parent::__construct($message, $code, $preException);
    }
}


trait traException
{
    private $_descriptions = [];

    public function throwException()
    {

    }


    public static function makeInfo($exception, $items = null)
    {
        $items = $items ? : ['code', 'message', 'file', 'line', 'trace', 'previous'];
        $info = [];
        foreach ($items as $item) {
            $method = ($item == 'trace') ? 'getTraceAsString' : sprintf('get%s', ucfirst($item));
            if (method_exists($exception, $method)) {
                $info[$item] = $exception->$method();
            } else {
                $info[$item] = null;
            }
        }
        if (isset ($info['code'])) {
            $info['code'] = self::parseCode($info['code']);
        }
        if (isset ($info['trace'])) {
            $info['trace'] = LF . str_replace("\n", LF, $info['trace']);
        }
        if (!empty($info['previous']) && method_exists($info['previous'], 'getInfo')) {
            $info['previous'] = '{' . LF . $info['previous']->getInfo() . LF . '}' . 'LF';
        }
        $ret = sprintf('[%s] Exception ', date('Y-m-d H:i:s'));
        foreach ($info as $key => $content) {
            $ret .= ucfirst($key) . ": $content" . LF;
        }
        return $ret;
    }

    public function getInfo()
    {
        return $this::makeInfo($this);
    }

    protected function setDescription($data)
    {
        $this->_descriptions = $data;
    }

    protected function makeMessage($exception, array $argus)
    {
        if (empty($this->_descriptions[$exception])) {
            $message = json_encode($argus);
        } else {
            $params = array_merge([$this->_descriptions[$exception]], $argus);
            $message = call_user_func_array('sprintf', $params);
        }
        return $message;
    }

    protected static function makeCode($type, $key)
    {
        $expCode = (intval($type) << 24) + intval($key);
        return $expCode;
    }

    protected static function parseCode($code)
    {
        $type = $code >> 24;
        $key = $code & ((1 << 24) - 1);
        return "$type.$key";
    }
}


