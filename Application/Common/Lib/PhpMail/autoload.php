<?php

function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $file = __DIR__ . DIRECTORY_SEPARATOR  .'src' . DIRECTORY_SEPARATOR . $path . '.class.php';
    $file = str_replace('\\\\', DIRECTORY_SEPARATOR, $file);
    if (file_exists($file)) {

        require_once $file;
    }
}
spl_autoload_register('classLoader');