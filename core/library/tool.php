<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-10-23
 */

namespace Core\library;


class tool
{
    const STR_BASE_64 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';

    public static function randomCode($length)
    {
        $strPool = self::STR_BASE_64;
        $code = '';
        while ($length--) {
            $offset = rand(0, 63);
            $code .= $strPool[$offset];
        }
        return $code;
    }

    public static function randomIP($isInt = false)
    {
        $ip = mt_rand();
        $ip = $isInt ? $ip : long2ip($ip);
        return $ip;
    }

    public static function ipRestrict(array $iptables)
    {
        $ip = \input::ip(false);
        $res = in_array($ip, $iptables);
        return $res;
    }

    public static function ip2region($ip)
    {
        static $codes;
        if (empty($codes)) {
            $regions = require_once(__DIR__.'/ip2region/regions.php');
            $codes = array_flip($regions);
        }
        require_once __DIR__ . '/ip2region/IP.class.php';
        $ip = is_int($ip) ? long2ip($ip) : $ip;
        $region = \IP::find($ip);
        if ($region == 'N/A' || empty($codes[$region[0]])) {
            $ret = '--';
        } else {
            $ret = $codes[$region[0]];
        }
        return $ret;
    }

    public static function code2region($code)
    {
        static $regions;
        if (empty($regions)) {
            $regions = require_once(__DIR__.'/ip2regions/regions.php');
        }
        if (isset ($regions[$code])) {
            $ret = $regions[$code];
        } else {
            $ret = '-';
        }
        return $ret;
    }
}
