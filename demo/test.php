<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/24
 * Time: 07:01
 */
include __DIR__."/../vendor/autoload.php";

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