<?php

namespace \local\classes;

// do configuration of the application
class Config {
    private static $valid_types = array(
        'root_dir',
        //'class_dirs',
        'autoloader',
        'log',
        'views',
        'database',
        'func_dir',
        'system'
    );
    public static $log_file;
    public static $view_dir;
    public static $root_dir;
    public static $class_dirs;
    public static $apc_cache;
    public static $db_info;
    public static $func_dir;
    public static $system = array();
    
    public static function init(array $config) {
        if (array_utils::values_eq_keys(self::$valid_types, $config)) {
            foreach (self::$valid_types as $type) {
                call_user_func('config::set_'.strtolower($type), $config[$type]);
            }
        }
    }
    
    private static function set_autoloader(array $class_dirs) {
        // set preload class dirs
        self::$class_dirs = $class_dirs;
        
        spl_autoload_register(function ($class) {
            $class_location = glob('{'.config::$root_dir.'/'.config::$class_dirs['base_dir'].'/'.$class.'.php,'.
                    config::$root_dir.'/'.config::$class_dirs['base_dir'].'/*/'.$class.'.php,'.
                    config::$root_dir.'/'.config::$class_dirs['controller_dir'].'/*/'.$class.'.php,'.
                    config::$root_dir.'/'.config::$class_dirs['models_dir'].'/*/'.$class.'.php}', GLOB_BRACE);
            
            if (count($class_location)  == 1) {
                require_once($class_location[0]);
            } else {
                logger::obj()->write('Unable to find class '.$class.' in configured directories', E_USER_ERROR);
            }
        });
    }
    
    private static function set_database($dsn) {
        // define global DB properties
        $db_required_values = array('user_name', 'user_pass', 'dbname', 'engine', 'host');

        // check db values in config for required values set
        if (array_utils::values_eq_keys($db_required_values, $dsn)) {
            trigger_error('Invalid database parameters set: '.__FILE__.' at lineno: '.__LINE__, E_USER_ERROR);
        } else {
            config::$db_info = $dsn;
            define('DB_DSN', $dsn['engine'].':dbname='.$dsn['dbname'].';host='.$dsn['host']);

            unset($dsn['engine'], $dsn['dbname'], $dsn['host']);

            foreach ($dsn as $var_name => $value) {
                define('DB_'.strtoupper($var_name), $value);
            }
        }
    }
    
    private static function set_apc($apc) {
        config::$apc_cache = $apc['apc_cache'];
    }
    
    private static function set_root_dir($dir_info) {
        self::set_dir(config::$root_dir, $dir_info);
    }
    
    private static function set_views(array $dir_info) {
        self::set_dir(config::$view_dir, $dir_info);
    }
    
    private static function set_func_dir(array $dir_info) {
        self::set_dir(self::$func_dir, $dir_info);
    }
    
    private static function set_dir(&$var, array $dir_info) {
        $var = self::$root_dir.'/'.$dir_info['dir'];
    }
    
    private static function set_log(array $log_info) {
        // test for writability
        if (fs_utils::is_writable($log_info['file'])) {
            config::$log_file = $log_info['file'];
        }
    }
    
    private static function set_system(array $system_info) {
        self::$system = $system_info;
    }
}
