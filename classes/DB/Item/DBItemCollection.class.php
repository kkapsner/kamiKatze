<?php

/**
 * Description of DBItemCollection
 *
 * @author kkapsner
 */
class DBItemCollection extends ViewableHTML implements ArrayAccess, SeekableIterator, Countable{
	/**
	 *
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

	public function getClass(){
		return $this->class;
	}
	
	// ArrayAccess interface
	/**
	 *
	 * @var array
	 */
	private $content = array();
	
	public function offsetExists($offset){
		return array_key_exists($offset, $this->content);
	}

	public function offsetGet($offset){
		return $this->content[$offset];
	}

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

	public function offsetUnset($offset){
		unset($this->content[$offset]);
	}
	
	
	// SeekableInterator interface
	/**
	 *
	 * @var int
	 */
	private $currentKey = 0;
	public function current(){
		return $this->content[$this->currentKey];
	}

	public function key(){
		return $this->currentKey;
	}

	public function next(){
		$this->currentKey++;
	}

	public function rewind(){
		$this->currentKey = 0;
	}

	public function seek($position){
		$this->currentKey = $position;
	}

	public function valid(){
		return $this->offsetExists($this->currentKey);
	}

	//Countable interface
	public function count(){
		return count($this->content);
	}
	
	// additional array functions
	public function pop(){
		return array_pop($this->content);
	}
	public function push($var /*, ...*/){
		$arguments = func_get_args();
		array_unshift($arguments, $this->content);
		return call_user_func_array("array_push", $arguments);
	}
	public function shift(){
		return array_shift($this->content);
	}
	public function unshift($var /*, ...*/){
		$arguments = func_get_args();
		array_unshift($arguments, $this->content);
		return call_user_func_array("array_unshift", $arguments);
	}
	public function splice($offset, $length = null, $replacement = null){
		$retArr = array_splice($this->content, $offset, $length, $replacement);
		$ret = new DBItemCollection($this->class);
		foreach ($retArr as $item){
			$ret[] = $item;
		}
		return $ret;
	}
	public function search($needle, $strict = false){
		return array_search($needle, $this->content, $strict);
	}
	public function contains($needle, $strict = false){
		return in_array($needle, $this->content, $strict);
	}

	/**
	 *
	 * @param array $arr
	 * @return DBItemCollection
	 */
	public static function fromArray(array $arr){
		if (count($arr)){
			$values = array_values($arr);
			$collection = new DBItemCollection(get_class($values[0]));

			foreach ($values as $item){
				$collection[] = $values;
			}

			return $collection;
		}
		else {
			throw new InvalidArgumentException("Array must not be empty.");
		}
	}
}

?>
