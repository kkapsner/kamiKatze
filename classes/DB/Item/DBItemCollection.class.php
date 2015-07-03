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
	 * @param string|null $class The class of the Colletion. If null is provided
	 *	the class of the first array item is used.
	 * @return DBItemCollection The created collection.
	 * @throws InvalidArgumentException
	 */
	public static function fromArray(array $arr, $class = null){
		$values = array_values($arr);
		if ($class === null){
			if (count($values)){
				$class = get_class($values[0]);
			}
			else {
				throw new InvalidArgumentException("Array must not be empty.");
			}
		}
		$collection = new DBItemCollection($class);

		foreach ($values as $item){
			$collection[] = $item;
		}

		return $collection;
	}
}

?>
