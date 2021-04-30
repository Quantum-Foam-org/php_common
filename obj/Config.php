<?php

namespace common\obj;

class Config implements \ArrayAccess, \Serializable  {

    protected $config = [];
    private static $instance = null;

    public function offsetExists($offset) {
        return property_exists($this, $offset);
    }

    public function offsetGet($offset) {
        if (!$this->offsetExists($offset)) {
            throw new \UnexpectedValueException('Offset, ' . $offset . ', does not exist');
        }
        return $this->$offset;
    }

    public function offsetSet($offset, $values) {
        if (property_exists($this, $offset)) {
            if (isset($this->config[$offset][0])) {
                try {
                    if (is_array($values)) {
                        $this->$offset = [];
                        foreach ($values as $value) {
                            $this->$offset[] = $this->filterOffset($offset, $value);
                        }
                    } else {
                        $this->$offset = $this->filterOffset($offset, $values);
                    }
                } catch (\UnexpectedValueException $e) {
                    throw $e;
                }
            } else {
                throw new \RuntimeException('Filter has not been set for offset ' . $offset);
            }
        } else {
            throw new \OutOfBoundsException($offset . ' does not exist for ' . __CLASS__);
        }
    }

    public function offsetUnset($offset) {
        if (!in_array($offset, array('config', 'instance'), TRUE) && $this->offsetExists($offset)) {
            $this->$offset = null;
        }
    }

    public function __get($offset) {
        return $this->offsetGet($offset);
    }

    public function __set($offset, $value) {
        try {
            $this->offsetSet($offset, $value);
        } catch (\OutOfBoundsException | \UnexpectedValueException | \RuntimeException $oe) {
            \common\logging\Logger::obj()->writeException($oe);
            throw $oe;
        }
    }

    public function __unset($offset) {
        $this->offsetUnset($offset);
    }
    
    public function __isset($offset) {
        return isset($this->$offset);
    }

    /**
     * The keys of Config::$config array must be protected variables of the class.
     * This will return a list of class variables that will be configured using offsetSet.
     * @return array - keys of Config::$config
     */
    protected function getConfiguredParams(): array {
        static $params = null;

        if ($params === null) {
            $params = array_keys($this->config);
        }

        return $params;
    }

    public function getArrayCopy() {
        $params = $this->getConfiguredParams();

        $arrayCopy = [];
        foreach ($params as $param) {
            $arrayCopy[$param] = $this->offsetGet($param);
        }

        return $arrayCopy;
    }

    public function exchangeArray(array $arguments) {
        $oldArray = $this->getArrayCopy();

        foreach ($arguments as $name => $value) {
            try {
                $this->offsetSet($name, $value);
            } catch (\OutOfBoundsException | \UnexpectedValueException | \RuntimeException $oe) {
                \common\logging\Error::handle($oe);
                throw $oe;
            }
        }
        
        unset($value);

        return $oldArray;
    }

    public static function obj(): Config {

        if (self::$instance === null) {
            $cls = get_called_class();
            self::$instance = new $cls();
        }

        return self::$instance;
    }

    private function filterOffset(string $offset, $value) {
        if (
                is_scalar($value) && ($nvalue = filter_var($value, 
                $this->config[$offset][0], 
                (isset($this->config[$offset][1]) && 
                        is_array($this->config[$offset][1]) ? 
                        $this->config[$offset][1] : 
                        array()))) === false
            ) {
            if (isset($this->config[$offset]['message'])) {
                $message = $this->config[$offset]['message'];
            } else {
                $message = 'Invalid value for ' . $offset;
            }
            throw new \UnexpectedValueException($message);
        }

        return $nvalue;
    }
    
     public function serialize() : string {
        return serialize($this->getArrayCopy());
     }
     
     public function unserialize(string $serialized) : void {
         $this->exchangeArray($this->unserialize($serialized));
     }
}
