<?php
/**
 * Created by PhpStorm.
 * User: yuyi
 * Date: 16/12/28
 * Time: 13:08
 */
include __DIR__."/../vendor/autoload.php";
$fs = new \Symfony\Component\Filesystem\Filesystem();

echo $fs->makePathRelative(
    "/Users/yuyi/Web/xiaoan/api",
"/Users/yuyi/Web/xiaoan/api"
   // "/Users/yuyi/Web/xiaoan/api/artisan"
);
