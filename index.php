<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-08
 * Time: 上午 10:16
 */
require_once "vendor/autoload.php";
$u = new \Http\Message\Uri();
var_dump($u->withUserInfo('adaaaa', 'f')->withScheme('https'));