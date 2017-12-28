<?php

function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $file = __DIR__ . DIRECTORY_SEPARATOR  . $path . '.php';
    $file = str_replace('\\\\', DIRECTORY_SEPARATOR, $file);

   /* print_r($file);
    echo '<hr/>';*/
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');