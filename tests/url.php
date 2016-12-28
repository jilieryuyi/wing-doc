<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/28
 * Time: 10:43
 */
$str = '@see http://www.php-fig.org/psr/psr-0/';
preg_match("/http[s]?\:\/\/[\.a-zA-Z0-9\/\-]{1,}/",$str,$match);
var_dump($match);
echo preg_replace("/http[s]?\:\/\/[\.a-zA-Z0-9\/\-]{1,}/",'<a target="__blank" href="$0">$0</a>',$str);