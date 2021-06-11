<?php 

namespace common\filters\Mongo;

use common\filters;

/**
 * Will validate a Mongo database Object Id field
 */
class ObjectIdFilter implements FilterInterface {
	public static function validate($id) {
		return true;
	}
}
