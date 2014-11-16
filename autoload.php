<?php


spl_autoload_register(function($class) {
	$classParts = explode('\\', $class);
	unset($classParts[0]);
	$classFile = __DIR__.implode('/', $classParts).'.php';
	var_dump($classFile);
	if (file_exists($classFile)) {
		require($classFile);
	}
	unset($classParts, $classFile);
});


$ini = parse_ini_file('config/init.ini', TRUE);

$ini['root_dir']['dir'] = __DIR__;

\common\Config::init($ini);