<?php

namespace common\logging;

class Error {
	
	public static function handle(\Exception $e) {
		return Logger::obj()->writeException($e);
	}
}