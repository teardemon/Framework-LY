<?php

/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-07-29
 */
class view
{

    public static function tpl($path)
    {
        return new viewTemplate($path);
    }


    public static function js($path, $minified = false)
    {
        $src = self::makeSource($path, 'js', $minified);
        echo "\t<script type='text/javascript' src='" . $src . "'></script>\n";
    }

    public static function css($path, $minified = false)
    {
        $src = self::makeSource($path, 'css', $minified);
        echo "\t<link type='text/css' rel='StyleSheet' href='" . $src . "' />\n";
    }

    public static function ng($tpl, array $ctls = null)
    {
        if ($ctls === null) {
            $ctls = [$tpl];
        }
        foreach ($ctls as $ctl) {
            $srcCtl = self::makeSource("ng/ctl/$ctl.js");
            echo "\t<script type='text/javascript' src='" . $srcCtl . "'></script>\n";
        }
        $srcTpl = self::makeSource("ng/tpl/$tpl.html");
        echo "\t<ng-include src=\"'$srcTpl'\"></ng-include>\n";
    }

    public static function debug($debugCode = DEBUG_CODE_CORE)
    {
        return new viewDebug($debugCode);
    }

    public static function version()
    {
        static $version;
        if (empty($version)) {
            $version = (SYS_ENV == ENV_DEV) ? time() : config::load('system', 'setting', 'version');
        }
        return $version;
    }

    public static function makeSource($path, $fix = null, $minified = false)
    {
        $version = self::version();
        if ($path[0] != '/') {
            $path = $fix . '/' . $path;
        }
        if ($minified === true && SYS_ENV != ENV_DEV) {
            $path .= '.min';
        }
        if ($fix) {
            $path .= ".$fix";
        }
        $src = HOST_RESOURCE . "/resource/$path?v=$version";
        $src = preg_replace('#/+#', '/', $src);
        return $src;
    }
}


class viewTemplate
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function __destruct()
    {
        $file = PATH_APP . "/template/$this->path.php";
        if (is_file($file)) {
            require $file;
        } else {
            throw new coreException(coreException::TEMPLATE_NOT_FOUND, [$this->path]);
        }
    }

    public function with($key, $value)
    {
        $this->$key = $value;
        return $this;
    }

    public function display()
    {
        $this->__destruct();
    }
}

class viewDebug
{
    private $debugCode;

    private $buffer = '';

    public function __construct($debugCode)
    {
        $this->debugCode = $debugCode;
    }

    public function __destruct()
    {
        output::debugStd($this->buffer, $this->debugCode);
    }

    public function autoRefresh(array $urls)
    {
        $jsonUrls = '["' . implode('","', $urls) . '"]';
        $this->buffer .= "
        <script type='text/javascript'>
            (function(){
                debug.autoRefresh($jsonUrls);
            })();
        </script>
        ";
    }

}