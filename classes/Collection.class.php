<?php
/**
 * Collection class definition
 */

/**
 * Class to hold a bunch of instances with a given class.
 *
 * @author Korbinian Kapsner
 */
class Collection extends ViewableHTML implements ArrayAccess, IteratorAggregate, /*SeekableIterator,*/  Countable{
	/**
	 * The allowed class in the collection.
	 * @var string
	 */
	protected $class;
	/**
	 * Constructor for Collection
	 * @param mixed $class Class name of the items in the collection.
	 */
	public function __construct($class){
		if (!class_exists($class)){
			throw new InvalidArgumentException("There is no class " . $class . ".");
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
	 * {@inheritdoc}
	 *
	 * @param int $offset
	 * @return mixed
	 */
	public function offsetExists($offset){
		return array_key_exists($offset, $this->content);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $offset
	 * @return mixed
	 */
	public function offsetGet($offset){
		return $this->content[$offset];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $offset
	 * @param mixed $value
	 * @throws InvalidArgumentException
	 */
	public function offsetSet($offset, $value){
		if (!($value instanceof $this->class)){
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
	 * {@inheritdoc}
	 *
	 * @param int $offset
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
	 * {@inheritdoc}
	 *
	 * @return DBItem
	 */
	public function current(){
		return $this->content[$this->currentKey];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function key(){
		return $this->currentKey;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next(){
		$this->currentKey++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind(){
		$this->currentKey = 0;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $position
	 */
	public function seek($position){
		$this->currentKey = $position;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return bool
	 */
	public function valid(){
		return $this->offsetExists($this->currentKey);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @return Iterator
	 */
	public function getIterator(){
		return new ArrayIterator($this->content);
	}


	//Countable interface
	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function count(){
		return count($this->content);
	}
	
	// additional array functions
	/**
	 * Checks if all items in an array are of the desired class for the collection.
	 * 
	 * @param array $arr
	 * @return boolean
	 */
	protected function checkArrayClasses($arr){
		foreach ($arr as $item){
			if (!($item instanceof $this->class)){
				return false;
			}
		}
		return true;
	}

	/**
	 * Removes the last element in the collection and returns it.
	 * @return <class> The popped element.
	 */
	public function pop(){
		return array_pop($this->content);
	}

	/**
	 * Appends one or more elements at the end of the collection.
	 * @param mixed $var
	 * @return int The new length of the collection
	 * @throws InvalidArgumentException
	 */
	public function push($var /*, ...*/){
		$arguments = func_get_args();
		if (!$this->checkArrayClasses($arguments)){
			throw new InvalidArgumentException("All values must be a " . $this->class . ".");
		}
		foreach ($arguments as $item){
			$this->content[] = $item;
		}
		return $this->count();
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
	 * @throws InvalidArgumentException
	 */
	public function unshift($var /*, ...*/){
		$arguments = func_get_args();
		if (!$this->checkArrayClasses($arguments)){
			throw new InvalidArgumentException("All values must be a " . $this->class . ".");
		}
		foreach ($arguments as $arg){
			array_unshift($this->content, $arg);
		}
		return $this->count();
	}

	/**
	 * Removes a part of the collection and replaces it by other elements.
	 * @param type $offset
	 * @param type $length
	 * @param type $replacement
	 * @return array containing the removed elements.
	 * @throws InvalidArgumentException
	 */
	public function splice($offset, $length = null, $replacement = null){
		if ($replacement !== null){
			if (!$this->checkArrayClasses($replacement)){
				throw new InvalidArgumentException("All values must be a " . $this->class . ".");
			}
		}
		$retArr = array_splice($this->content, $offset, $length, $replacement);
		$class = get_class($this);
		$ret = new $class($this->class);
		call_user_func_array(array($ret, "push"), $retArr);
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
	 * Creates an array out of the collection
	 * @return array
	 */
	public function toArray(){
		return $this->content;
	}

	/**
	 * Creates a Collection out of an array.
	 * @param array $arr The array containing
	 * @return Collection The created collection.
	 * @throws InvalidArgumentException
	 */
	public static function fromArray(array $arr){
		if (count($arr)){
			$values = array_values($arr);
			$collection = new Collection(get_class($values[0]));

			foreach ($values as $item){
				$collection[] = $item;
			}

			return $collection;
		}
		else {
			throw new InvalidArgumentException("Array must not be empty.");
		}
	}
	
	public function view($context = false, $output = false, $args = false){
		$ret = parent::view($context, $output, $args);
		if (!$ret){
			$context = "collection|" . preg_replace("/(^|\\|)/", "$1collection.", $context);
			return parent::viewByName($this->class, $context, $output, $args);
		}
		else {
			return $ret;
		}
	}
}

?>
