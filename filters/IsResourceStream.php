<?php 

namespace common\filters;


class IsResourceStream {
	public static function validate($stream) {
		return (is_resource($stream) === TRUE ? $stream : FALSE);
	}
}

?>