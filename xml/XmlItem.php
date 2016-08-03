<?php

namespace common\xml;

use \common\object\Config as objectConfig;

class RssItem extends objectConfig {
	protected $nodeName, $textContent;
	protected $config = array(
			'nodeName' => array(FILTER_SANITIZE_FULL_SPECIAL_CHARS),
			'textContent' => array(FILTER_SANITIZE_FULL_SPECIAL_CHARS)
			);
}