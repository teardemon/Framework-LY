<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-26
 */

define('PATH_ROOT', dirname(dirname(__DIR__)));

define('PATH_CORE', PATH_ROOT . '/core');
define('PATH_CONFIG', PATH_ROOT . '/config');
define('PATH_APP', PATH_ROOT . '/application');

//running environment
const ENV_DEV = 'DEV'; // develop
const ENV_PDC = 'PDC'; // produce
const ENV_OLT = 'OLT'; // online test
const ENV_LCT = 'LCT'; // local test

//boot mode
const BOOT_CGI = 'CGI';
const BOOT_CLI = 'CLI';
const BOOT_TEST = 'TEST';

//debug code
define('DEBUG_CODE_ALL', ~0);
const DEBUG_CODE_CORE = 1;
const DEBUG_CODE_MYSQL = 2;
const DEBUG_CODE_CURL = 4;

require_once PATH_CORE . '/io.php';
require_once PATH_CORE . '/config.php';
require_once PATH_CORE . '/router.php';
require_once PATH_CORE . '/view.php';
require_once PATH_CORE . '/extension.php';
require_once PATH_CORE . '/exception.php';

spl_autoload_register('boot::autoLoad');

class boot
{
    public static function init($scope = null)
    {
        if (!defined('BOOT_MODE')) {
            throw new coreException(coreException::BOOT_MODE_IS_UNDEFINED);
        }
        define('BOOT_PREFIX', $scope);
        define('SYS_ENV', config::load('system', 'setting', 'env'));

        //line feed
        if (!defined('LF')) {
            define('LF', "\n");
        }

        //debug mode
        if (!defined('DEBUG_MODE')) {
            define('DEBUG_MODE', config::load('system', 'setting', 'debug_mode', 1));
        }

        //resource host
        if (!defined('HOST_RESOURCE')) {
            define('HOST_RESOURCE', config::load('system', 'setting', 'host_resource', ''));
        }
        input::init();
    }

    public static function run($prefix)
    {
        boot::init($prefix);
        $locator = router::locate($prefix);
        $res = router::parseLocator($locator);
        $control = $res['control'];
        $method = $res['method'];
        $params = $res['params'];
        if (class_exists($control)) {
            $controller = new $control;
        } else {
            throw new coreException(coreException::CONTROLLER_NOT_FOUND, [$control]);
        }
        if (method_exists($controller, $method)) {
            if (method_exists($controller, 'runBefore')) {
                $controller->runBefore();
            }
            call_user_func_array([$controller, $method], $params);
            if (method_exists($controller, 'runBehind')) {
                $controller->runBehind();
            }
        } else {
            throw new coreException(coreException::METHOD_NOT_FOUND, [$control, $method]);
        }
    }

    public static function autoLoad($name)
    {
        $namescopes = config::load('system', 'namescope');

        $frags = explode('\\', trim($name, '\\'));
        $scope = $frags[0];
        if (isset($namescopes[$scope])) {
            $frags[0] = $namescopes[$scope];
        }
        $_path = implode('/', $frags) . '.php';
        $path = PATH_ROOT . $_path;
        if (is_file($path)) {
            require_once($path);
        } elseif (BOOT_MODE != BOOT_TEST) {
            throw new coreException(coreException::AUTOLOAD_FAILED, [$_path]);
        }
    }

    /**
     * load all possible file in scope
     * @param $scope
     * @return int count of included files
     */
    public static function import($scope)
    {
        $pattern = PATH_ROOT . '/' . $scope;
        $files = glob($pattern);
        foreach ($files as $file) {
            include_once $file;
        }
        return count($files);
    }
}