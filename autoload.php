<?php

date_default_timezone_set('UTC');



//require_once($ini['root_dir']['dir'].'/classes/config.php');
//require_once($ini['root_dir']['dir'].'/classes/utility/array_utils.php');



spl_autoload_register(function($class) {
	$classParts = explode('\\', $class);
	unset($classParts[0]);
	$classFile = __DIR__.'/class/'.implode('/', $classParts).'.php';
	var_dump($classFile);
	if (file_exists($classFile)) {
		require($classFile);
	}
	unset($classParts, $classFile);
});


$ini = parse_ini_file('config/init.ini', TRUE);

$ini['root_dir']['dir'] = __DIR__;

\local\classes\Config::init($ini);