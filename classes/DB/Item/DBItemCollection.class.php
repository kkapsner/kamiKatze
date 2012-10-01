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
class DBItemCollection extends ViewableHTML implements ArrayAccess, SeekableIterator, Countable{
	/**
	 * The allowed class in the collection.
	 * @var string
	 */
	protected $class;
	/**
	 *
	 * @param mixed $class Class name of the items in the collection. Must be a subclass of DBItem..
	 */
	public function __construct($class){
		if (!is_subclass_of($class, "DBItem")){
			throw new InvalidArgumentException("Collection class must be a subclass of DBItem. " . $class . " given.");
		}
		$this->class = $class;
	}

	/**
	 * Returns the allowed class name.
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
	
	// ArrayAccess interface
	/**
	 * The containing items.
	 * @var array
	 */
	private $content = array();

	/**
	 *
	 */
	public function offsetExists($offset){
		return array_key_exists($offset, $this->content);
	}

	/**
	 *
	 */
	public function offsetGet($offset){
		return $this->content[$offset];
	}

	/**
	 *
	 * @throws InvalidArgumentException
	 */
	public function offsetSet($offset, $value){
		if (!is_a($value, $this->class)){
			throw new InvalidArgumentException("Value must be a " . $this->class . ".");
		}
		
		if ($offset === null){
			$this->content[] = $value;
		}
		else {
			if (!is_int($offset)){
				throw new InvalidArgumentException("Offset must be an integer.");
			}
			if ($offset < 0 || $offset >= $this->count()){
				throw new InvalidArgumentException("Offset out of range.");
			}
			$this->content[$offset] = $value;
		}
	}

	/**
	 *
	 */
	public function offsetUnset($offset){
		unset($this->content[$offset]);
	}
	
	
	// SeekableInterator interface
	/**
	 * Curent position in array.
	 * @var int
	 */
	private $currentKey = 0;

	/**
	 *
	 */
	public function current(){
		return $this->content[$this->currentKey];
	}

	/**
	 *
	 */
	public function key(){
		return $this->currentKey;
	}

	/**
	 *
	 */
	public function next(){
		$this->currentKey++;
	}

	/**
	 *
	 */
	public function rewind(){
		$this->currentKey = 0;
	}

	/**
	 *
	 */
	public function seek($position){
		$this->currentKey = $position;
	}

	/**
	 *
	 */
	public function valid(){
		return $this->offsetExists($this->currentKey);
	}

	//Countable interface
	/**
	 *
	 */
	public function count(){
		return count($this->content);
	}
	
	// additional array functions
	/**
	 * Removes the last element in the collection and returns it.
	 * @return DBItem The popped element.
	 */
	public function pop(){
		return array_pop($this->content);
	}

	/**
	 * Appends one or more elements at the end of the collection.
	 * @param mixed $var
	 * @return int The new length of the collection
	 */
	public function push($var /*, ...*/){
		$arguments = func_get_args();
		array_unshift($arguments, $this->content);
		return call_user_func_array("array_push", $arguments);
	}

	/**
	 * Removes the first element in the collection and returns it.
	 * @return DBItem The shifted element.
	 */
	public function shift(){
		return array_shift($this->content);
	}

	/**
	 * Prepends one or more elements at the start of the collection.
	 * @param type $var
	 * @return The new lengh of the collection
	 */
	public function unshift($var /*, ...*/){
		$arguments = func_get_args();
		array_unshift($arguments, $this->content);
		return call_user_func_array("array_unshift", $arguments);
	}

	/**
	 * Removes a part of the collection and replaces it by other elements.
	 * @param type $offset
	 * @param type $length
	 * @param type $replacement
	 * @return array containing the removed elements.
	 */
	public function splice($offset, $length = null, $replacement = null){
		$retArr = array_splice($this->content, $offset, $length, $replacement);
		$ret = new DBItemCollection($this->class);
		foreach ($retArr as $item){
			$ret[] = $item;
		}
		return $ret;
	}

	/**
	 * Searches in the collection for a specific element.
	 * @param mixed $needle The element to be searched.
	 * @param bool $strict If the comparision should be performed in strict mode or not.
	 * @return int|false The index of the searched element if found and false otherwise.
	 */
	public function search($needle, $strict = false){
		return array_search($needle, $this->content, $strict);
	}

	/**
	 * Checks if a element is in the collection or not.
	 * @param mixed $needle The element to be searched.
	 * @param bool $strict If the comparision should be performed in strict mode or not.
	 * @return bool If the element is in the collection.
	 */
	public function contains($needle, $strict = false){
		return in_array($needle, $this->content, $strict);
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
