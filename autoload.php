<?php

spl_autoload_register(function ($className) {
    $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $fileName = ltrim($fileName, 'Lyz' . DIRECTORY_SEPARATOR);
    $fileName = substr_replace($fileName, 'src' . DIRECTORY_SEPARATOR, strpos($fileName, DIRECTORY_SEPARATOR) + 1, 0);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $fileName . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
