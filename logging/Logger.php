<?php

namespace common\logging;

class Logger {
	
    private static $file_handle = null;
    private static $date = null;
    private static $type = array(
        -1 => 'ERROR',
        0 => 'INFORMATION',
        1 => 'WARNING',
    );
    protected static $instance = null;
	
    public static function obj() : Logger {
		
        if (self::$instance === null) {
            self::$instance = new Logger();
            self::$file_handle = \common\Config::obj()->log_file;
            if (!self::$file_handle instanceOf \SplFileObject) {
                die("no log file configured\n");
            }
            self::$date = new \DateTime('now');
        }
        return self::$instance;
    }
    
    public function writeException(\Throwable $e, $type = -1) : int {
        $this->write(get_class($e).' (FILE: '.$e->getFile().') (LINE: '.$e->getLine().'): '.$e->getMessage(), $type);
    	
        return $e->getLine();
    }
    
    public function writeDebug(string $message, int $type = 0) : int
    {
        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        
        $this->write('(FILE: '.$debug[0]['file'].') (LINE: '.$debug[0]['line'].'): '.$message, $type);
    
        return $debug[0]['line'];
    }
    
    public function write(string $message, int $type = 0) : void {
        if (\common\Config::obj()->system['debug'] === "1") {
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