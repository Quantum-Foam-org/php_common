<?php

namespace common\logging;

class Error {
	
	public static function handle(\Exception $e) {
		Logger::obj()->writeException($e);
	}
}