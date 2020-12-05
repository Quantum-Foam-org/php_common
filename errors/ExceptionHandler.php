<?php

namespace common\errors;

use common\logging\Logger;


class ExceptionHandler {

    /**
     * 
     * @param \common\errors\Exception $e
     */
    public static function handler(Exception $e) {
        echo "Uncaught exception: ", $e->getMessage(), "\n";
        
        Logger::obj()->writeException($e);
    }

    /**
     * 
     * @throws Exception
     */
    public static function setHander() {
        \set_exception_handler('ExceptionHandler::handler');

        throw new Exception('Uncaught Exception');
        echo "Not Executed\n";
    }
}
