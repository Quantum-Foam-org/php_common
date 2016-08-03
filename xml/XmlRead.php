<?php
namespace common\xml;

use \common\collections\xmlItemStorage as xmlItemStorage;
use \common\logging\Logger as Logger;

class XmlRead extends \DomDocument
{
    private $url = null;
    
    private $xmlItems = [];
    
    public static function readFromUrl($url)
    {
        $url = \filter_var($url, FILTER_VALIDATE_URL);
        
        if ($url !== FALSE) {
            $cl = \get_called_class();
            $xmlRead = new $cl();
            $xmlRead->load($url, LIBXML_NOBLANKS);
        } else {
            $xmlRead = null;
        }
        
        unset($url);
        
        return $xmlRead;
    }
    
    public function getNodes($nodeName)
    {
        if (count($this->xmlItems) === 0) {
            foreach ($this->getElementsByTagName($nodeName) as $xmlItem) {
                $xmlItems = $this->xmlItems[] = new xmlItemStorage();
                foreach ($xmlItem->childNodes as $childNode) {
                    $xmlItem = new XmlItem();
                    $xmlItem->nodeName = $childNode->nodeName;
                    $xmlItem->textContent = $childNode->textContent;
                    try {
                        $xmlItems->attach($xmlItem);
                    } catch (\UnexpectedValueException $ue) {
                        Logger::obj()->writeException($ue);
                    }
                }
            }
            unset($xmlItem);
        }
        return $this->xmlItems;
    }
    
    public function __destruct()
    {
        unset($this->xmlItems, $this->url);
    }
}