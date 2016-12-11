<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-08
 * Time: 上午 10:16
 */
require_once "vendor/autoload.php";
$u = new \Http\Message\Uri('HTTPs://user@www.baidu.com:443/fdsaf1/fdsf2/fw3/g.pdf?gyuyu=890&huih=09#section1');
var_dump($u->__toString());