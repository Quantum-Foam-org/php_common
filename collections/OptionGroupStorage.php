<?php

namespace \local\classes\collections;


class OptionGroupStorage extends SplObjectStorage {
	protected $objectType = null;
	
	/**
	 * Add all OptionGroupStorage items
	 * @param OptionGroupStorage $storage - an instance of OptionGroupStorage
	 */
	public function addAll(OptionGroupStorage $storage) {
		parent::addAll($storage);
	}
	
	/**
	 * Ensures that $object is of type OptionGroupStorage $objectType
	 * @throws UnexpectedValueException when the object is not of type $objectType
	 */
	public function attach($object, $data = null) {
		if (!($object instanceOf $this->objectType)) {
			throw new UnexpectedValueException('Invalid object type '.get_called_class().'::'.__METHOD__.': '.get_class($object));
		}
		
		parent::attach($object, $data);
	}
	
	/**
	 * Will remove all of stored items from $storage
	 * @param $storage an object of OptionGroupStorage
	 */
	public function removeAll(OptionGroupStorage $storage) {
		parent::removeAll($storage);
	}
	
	/**
	 * Will remove all of stored items except items from $storage
	 * @param $storage an object of OptionGroupStorage
	 */
	public function removeAllExcept(OptionGroupStorage $storage) {
		parent::removeAllExcept($storage);
	}
}