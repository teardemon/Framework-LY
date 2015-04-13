<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-26
 * bootstrap from CLI
 */

require __DIR__ . '/bootstrap.php';

//run in command line interface
define('BOOT_MODE', BOOT_CLI);
define('LF', "\n");
try {
    $_SERVER['REQUEST_URI'] = $argv[1];
    boot::run('/');
} catch (Exception $e) {
    echo coreException:: makeInfo($e);
}
