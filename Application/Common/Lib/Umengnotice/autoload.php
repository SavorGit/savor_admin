<?php

function classLoader($class)
{
    var_dump($class);
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    var_dump($path);
    //不和阿里一样有src目录所以不用和阿里云一样那样写
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR. $path . '.php';
    var_dump($file);
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');