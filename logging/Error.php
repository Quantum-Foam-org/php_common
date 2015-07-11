<?php

namespace common\logging;

class Error {
	
	public static function handle(\Exception $e) {
		Logger::obj()->write(get_class($e).' (FILE: '.$e->getFile().') (LINE: '.$e->getLine().'): '.$e->getMessage(), -1);
	}
}