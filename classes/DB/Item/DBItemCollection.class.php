<?php
/**
 * DBItemCollection class definition
 */

/**
 * Class to hold a bunch of DBItems.
 *
 * @author Korbinian Kapsner
 * @package DB\Item
 */
class DBItemCollection extends Collection{
	/**
	 * Constructor for DBItemCollection
	 * @param mixed $class Class name of the items in the collection. Must be a subclass of DBItem..
	 */
	public function __construct($class){
		if (!is_subclass_of($class, "DBItem")){
			throw new InvalidArgumentException("Collection class must be a subclass of DBItem. " . $class . " given.");
		}
		$this->class = $class;
	}

	/**
	 * Creates a DBItemCollection out of an array.
	 * @param array $arr The array containing
	 * @return DBItemCollection The created collection.
	 * @throws InvalidArgumentException
	 */
	public static function fromArray(array $arr){
		if (count($arr)){
			$values = array_values($arr);
			$collection = new DBItemCollection(get_class($values[0]));

			foreach ($values as $item){
				$collection[] = $item;
			}

			return $collection;
		}
		else {
			throw new InvalidArgumentException("Array must not be empty.");
		}
	}
}

?>
