<?php

/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-28
 */

class input
{
    private static $data;

    public static function init()
    {
        self::$data = $GLOBALS;
    }

    public static function cli($name, $default=null)
    {
        if (empty(self::$data['_CLI'])) {
            global $argv;
            $argv = $argv ?: [];
            self::$data['_CLI'] = parseArgv($argv);
        }
        return self::makeInput('_CLI', $name, $default);
    }

    public static function get($name, $default = null)
    {
        return self::makeInput('_GET', $name, $default);
    }

    public static function post($name, $default = null)
    {
        return self::makeInput('_POST', $name, $default);
    }

    public static function postJson($name, $default = null)
    {
        if (empty(self::$data['_POST_JSON'])) {
            $input = phpStream::input();
            self::$data['_POST_JSON'] = json_decode($input, true);
        }
        return self::makeInput('_POST_JSON', $name, $default);
    }

    public static function postRawData($default = null)
    {
        return self::makeInput('HTTP_RAW_POST_DATA', null, $default);
    }

    public static function cookie($name, $default = null)
    {
        return self::makeInput('_COOKIE', $name, $default);
    }

    public static function session($name, $default = null)
    {
        return self::makeInput('_SESSION', $name, $default);
    }

    public static function ip($toInt=true)
    {
		$keys = ['HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_REMOTE_HOST', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        $ip = false;
        foreach ($keys as $key) {
            if (isset ($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                break;
            }
        }
        $ipInt = ip2long($ip);
        $ip = $toInt ? sprintf('%u', $ipInt) : long2ip($ipInt);
        return $ip;
    }


    /**
     * @param $origins
     * @param null $name
     * @param null $default
     * @return objInput
     */
    private static function makeInput($origins, $name = NULL, $default = NULL)
    {
        $origins = (array)$origins;
        $val = NULL;
        foreach ($origins as $origin) {
            if (isset (self::$data[$origin])) {
                if ($name === NULL) {
                    $val = self::$data[$origin];
                } elseif (isset (self::$data[$origin][$name])) {
                    $val = self::$data[$origin][$name];
                }
                break;
            }
        }
        $obj = objInput::instance($name, $val)->setDefault($default);
        return $obj;
    }

}

class objInput
{
    private $name;
    private $value;

    public static function instance($name, $value = null)
    {
        $inst = new self;
        $inst->name = $name;
        $inst->value = $value;
        return $inst;
    }

    public function name()
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDefault($value)
    {
        $this->value = $this->value ? : $value;
        return $this;
    }

    public function filter($filter, $params = NULL)
    {
        if (!is_array($params)) {
            $params = [$this->value, $params];
        } else {
            array_unshift($params, $this->value);
        }
        $this->value = call_user_func_array($filter, $params);
        return $this;
    }
}

class output
{
    private static $instance;

    private $pathLog;

    private $delayedLogs = [];

    private static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
            self::$instance->pathLog = config::load('system', 'path', 'log');
        }
        return self::$instance;

    }

    public function __destruct()
    {
        foreach ($this->delayedLogs as $key => $messages) {
            foreach ($messages as $message) {
                self::log($key, $message);
            }
        }
    }

    public static function log($key, $message, $delay = false)
    {
        $inst = self::instance();
        if (is_array($message)) {
            $message = json_encode($message);
        }
        if ($delay == true) {
            $inst->delayedLogs[$key][] = $message;
        } else {
            $file = sprintf('%s/%s.log', $inst->pathLog, $key);
            $message = sprintf('[%s] %s', date('Y-m-d H:i:s'), $message . "\n");
            file_put_contents($file, $message, FILE_APPEND);
        }
    }

    /**
     * @param string $file
     * @param string $message
     * @param int $debugCode
     */
    public static function debugFile($file, $message, $debugCode = DEBUG_CODE_CORE)
    {
        if ($debugCode & DEBUG_MODE) {
            file_put_contents($file, $message, FILE_APPEND);
        }
    }

    public static function debugStd ($message, $debugCode = DEBUG_CODE_CORE)
    {
        if ($debugCode & DEBUG_MODE) {
            echo $message;
        }
    }

    public static function console()
    {

    }

    public static function cookie($name, $value, $duration, $path, $domain)
    {
        static $prefix;
        if (empty($prefix)) {
            $prefix = config::load('system', 'settign', 'cookie_prefix');
        }
        $name = $prefix . $name;
        $expire = is_int($duration) ? time() + $duration : strtotime($duration);
        setcookie($name, $value, $expire, $path, $domain);
    }

    public static function session($name, $value)
    {

    }


}

class phpStream
{
    public static function input()
    {
        return file_get_contents('php://input');
    }

    public static function stdin()
    {
        return file_get_contents('php://stdin');
    }
}