<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-15
 */

require_once __DIR__.'/bootstrap.php';
$argus = parseArgv($_SERVER['argv']);
$debugMode = isset ($argus['debug-mode']) ? $argus['debug-mode'] : 0;
define('DEBUG_MODE', $debugMode);
define('BOOT_MODE', BOOT_TEST);
boot::init();