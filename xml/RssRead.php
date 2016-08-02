<?php

namespace common\xml;

use \common\collections\RssItems as rssItems;
use \common\logging\Logger as Logger;


class RssRead extends XmlRead {
	private $url = null;
	private $rssItems = [];
	
	private function getNodes() {
		return $this->getElementsByTagName('item');
	}
	
	private function getItem() {
		foreach ($this->getNodes() as $xmlItem) {
			$rssItems = $this->rssItems[] = new RssItems();
			foreach ($xmlItem->childNodes as $childNode) {
				$rssItem = new RssItem();
				$rssItem->nodeName = $childNode->nodeName;
				$rssItem->textContent = $childNode->textContent;
				$rssItems->attach($rssItem);
			}
		}
		unset($xmlItem);
	}
	
	public function getItems() {
		if (count($this->rssItems) === 0) {
			$this->getItem();
		}
		return $this->rssItems;
	}
}
