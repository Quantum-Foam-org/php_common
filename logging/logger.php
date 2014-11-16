<?php

class logger {

    private static $file_handle = null;
    private static $date = null;
    private static $type = array(
        -1 => 'ERROR',
        0 => 'INFORMATION',
        1 => 'WARNING',
    );
    protected static $instance = null;

    public static function obj() {

        if (self::$instance === null) {
            self::$instance = new logger();
            self::$file_handle = new SplFileObject(config::$log_file, 'a+');
            self::$date = new DateTime('now');
        }

        return self::$instance;
    }

    public function write($message, $type = 0) {
        if ((boolean)config::$system['debug'] == TRUE) {
        	if (!self::$file_handle->flock(LOCK_EX)) {
        		usleep(1);
				$this->write($message, $type);
        	} else {
	            if (isset(self::$type[$type])) {
	                $type = self::$type[$type];
	            } else {
	                $type = self::$type[0];
	            }
	
	            $date = self::$date->setTimestamp(time())->format('Y-m-d H:i:s');
	
	            if (is_array($message)) {
	                $message = var_export($message, 1);
	            }
	
	            self::$file_handle->fwrite(vsprintf("[%s]\t\t-\t\t%s - %s \n", array($date, $type, $message)));
			}
        }
    }

}