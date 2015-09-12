<?php

namespace common;

// do configuration of the application
class Config
{

    private $valid_types = array(
        'log',
        'system',
        'php'
    );
    private $log_file;
    private $system = [];
    private $php_value = [];

    private static $instance = null;
    
    public static function obj($file = null)
    {
        if ($file !== null)
        {
            $file = new \SplFileInfo($file);
            
            if (!$file->isReadable())
                throw new \RuntimeException('File does not exist');

     
            $config = parse_ini_file($file->getRealPath(), TRUE);

            self::$instance = new Config();

            foreach ($config as $type => $value)
            {
                if (in_array($type, self::$instance->valid_types))
                {
                    self::$instance->{'set_' . strtolower($type)}($config[$type]);
                }
            }
            unset($file, $config, $type, $value);
        }
        return self::$instance;
    }

    private function set_log(array $logInfo)
    {
        $file = new \SplFileObject($logInfo['file'], 'a');
        
        if (!$file->isWritable())
            throw new \RuntimeException('File does not exist');
        
        $this->log_file = $file;
    }

    private function set_system(array $system_info)
    {
        if (!isset($system_info['timezone']))
        {
            $system_info['timezone'] = 'UTC';
        }
        $this->system = $system_info;
        date_default_timezone_set($system_info['timezone']);
    }
    
    private function set_php_value(array $phpValue)
    {
        foreach ($phpValue as $k => $value)
        {
            if (ini_set($k, $value) !== FALSE)
            {
                $this->php_value[$k] = $value;
            }
            else
            {
                Logger::obj()->writeDebug('Cannot set PHP VALUE: ' . $k . ' -> ' . $value, 1);
            }
        }
        unset($k, $value);
    }
    
    public function __get($name)
    {
        if (!property_exists($this, $name))
        {
            throw new \UnexpectedValueException('Property '.$name.' does not exist');
        }
        return $this->$name;
    }
}
