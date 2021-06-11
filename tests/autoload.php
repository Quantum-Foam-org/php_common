<?php
namespace common;

spl_autoload_register(function ($class) {
    $namespace = 'common\tests\\';
    
    if (strpos($class, $namespace) === 0) {
        $class = substr($class, strlen($namespace)-1);
        
        $classFile = realpath(__DIR__ . DIRECTORY_SEPARATOR .'classes'. str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
        
        if (file_exists($classFile)) {
            require ($classFile);
        }
        unset($classFile);
    }
});
        