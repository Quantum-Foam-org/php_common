<?php

namespace common;

// do configuration of the application
class Config {
    private static $valid_types = array(
        'log',
        'system'
    );
    public static $log_file;
    public static $system = array();
    
    public static function init(array $config) {
		foreach (self::$valid_types as $type) {
			call_user_func('self::set_'.strtolower($type), $config[$type]);
		}
    }
    
    private static function set_log(array $log_info) {
        // test for writability
        if (is_writable(\realpath(\dirname($log_info['file'])))) {
            self::$log_file = $log_info['file'];
        }
    }
    
    private static function set_system(array $system_info) {
        self::$system = $system_info;
    }
}
