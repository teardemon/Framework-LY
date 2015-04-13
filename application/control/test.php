<?php
/**
 * Created by PhpStorm.
 * Author: LyonWong
 * Date: 2014-10-15
 */

namespace CTL;


use Core\library\mysql;
use Core\library\email;
use Core\library\redis;

class test
{
    public function cli($name)
    {
        var_dump (\input::cli($name, 'default')->value());
    }

    public function mysql()
    {
        $inst = mysql::instance('test');
        $res = $inst->show("databases")->fetchAll();
        print_r($res);
    }

    public function input()
    {
        $res = \phpStream::input();
        var_dump ($res);
    }

    public function redis()
    {
        $redis = redis::instance('test');
        $redis->set('foo', 'Hello redis!');
        $res = $redis->get('foo');
        var_dump ($res);

    }

    public function email()
    {
        $mail = email::instance('noreply');
        $mail->Port = 465;
        $mail->addAddress('huangl@gamebegin.com');
        $mail->Subject = "Test";
        $mail->Body = "This is a test mail.";
        $mail->Send();
    }

} 