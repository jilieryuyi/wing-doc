<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 07:01
 */
include __DIR__."/../vendor/autoload.php";


function get_millisecond()
{
    $time = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($time[0]) + floatval($time[1])) * 1000);
}

$start_time = get_millisecond();

$app = new \Wing\Doc\Doc(
    "/Users/yuyi/Web/xiaoan/api",
    "/Users/yuyi/Web/xiaoan/wing/doc"
);
$app->addExcludePath([
    "vendor/*","Config/*","config/*",
    "public/*","database/*","tests/*"
]);
$app->addExcludeFileName([
    "artisan","composer","app.php","web.php"
]);
$app->run();


echo "完成，耗时".(get_millisecond()-$start_time)."毫秒\r\n";