<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-26
 * bootstrap from web server
 */

require __DIR__.'/bootstrap.php';

//run in web server
define('BOOT_MODE', BOOT_CGI);
define('LF', "<br>\n");
try {
    boot::run(CTL_PREFIX);
} catch (Exception $e) {
    if (SYS_ENV == ENV_DEV) {
        echo coreException::makeInfo($e);
    }
}

