<?php

/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-08-02
 */
class config
{
    static private $data = [];

    public static function load($module, $section = null, $item = null, $default = null)
    {
        //load module
        if (!isset ($data[$module])) {
            $path = PATH_CONFIG . '/.conf/' . $module . '.php';
            if (is_file($path)) {
                self::$data[$module] = require($path);
            } elseif ($default !== null) {
                return $default;
            } else {
                throw new coreException(coreException::CONFIG_NOT_FOUND, [$module]);
            }
        }
        $config = self::$data[$module];

        //load section
        if ($section !== null) {
            if (isset ($config[$section])) {
                $config = $config[$section];
            } elseif ($default !== null) {
                return $default;
            } else {
                throw new coreException(coreException::CONFIG_NOT_FOUND, [$module . '-' . $section]);
            }
        }

        //load item
        if ($item !== null) {
            if (isset($config[$item])) {
                $config = $config[$item];
            } elseif ($default !== null) {
                return $default;
            } else {
                throw new coreException(coreException::CONFIG_NOT_FOUND, [$module . '-' . $section . '-' . $item]);
            }
        }

        return $config;
    }

    public static function parse($conf)
    {
        $pattern = PATH_CONFIG . '/' . $conf . '/*';
        $paths = glob($pattern);
        $config = [];
        foreach ($paths as $path) {
            $pathinfo = pathinfo($path);
            if (is_dir($path)) {
                $_config = self::parse("$conf/{$pathinfo['basename']}");
                $config = array_merge($config, $_config);
            } elseif ($pathinfo['extension'] == 'ini') {
                $module = strstr($conf,'/').'/'.$pathinfo['filename'];
                $config[$module] = parse_ini_file($path, true);
            }
        }
        return $config;
    }

    /**
     * @return int count of parsed files
     */
    public static function init()
    {
        //parse
        $list = ['default', 'common', 'local']; // priority ascending
        $config = [];
        foreach ($list as $conf) {
            $_config = self::parse($conf);
            $config = arrayMergeForce($config, $_config);
        }

        //write
        $pathConf = PATH_CONFIG . '/.conf';
        foreach ($config as $module => $data) {
            $fileConf = $pathConf . $module . '.php';
            $dirname = dirname($fileConf);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0777, true);
            }
            $module = str_replace('/', '_', $module);
            $dataConf = "<?php\n\$CONF$module=" . var_export($data, true) . ";\nreturn \$CONF$module;\n";
            file_put_contents($fileConf, $dataConf);
        }

        $pathConfigJS = PATH_APP . '/resource/js/config.js';
        $jsConf = [
            'environment' => self::load('system', 'setting', 'env'),
            'version' => self::load('system', 'setting', 'version'),
            'resourcePath' => self::load('system', 'setting', 'resource_path'),
        ];
        $dataConfigJS = 'var config=' . json_encode($jsConf) . ';';
        file_put_contents($pathConfigJS, $dataConfigJS);

        return count($config);
    }

}