<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-12-08
 * Time: 上午 10:16
 */
require_once "vendor/autoload.php";
$u = new \Http\Message\Uri();
//var_dump($u->withUserInfo('adaaaa', 'f')->withScheme('https'));
$url = 'https://toolshttp:@stackoverflow.com/questions/11029683/double-e?test=dfs&fds=2%ncode-for-json#section-2';
var_dump(parse_url($url));
var_dump(rawurldecode($url));