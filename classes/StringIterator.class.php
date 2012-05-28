<?php

/**
 *
 * @author kkapsner
 */
class StringIterator implements SeekableIterator, ArrayAccess, Countable, Serializable{

	/**
	 * Parse a collection of chars to a string of the contained chars. I.E. "a-f" gets "abcdef".
	 * @param string $chars
	 * @return string
	 */
	public static function parseCharCollection($chars){
		if (is_string($chars)){
			return preg_replace_callback('/(.)-(.)/', array("StringIterator", "parseCharCollection"), $chars);
		}
		else if (is_array($chars)){
			$ret = "";
			$start = ord($chars[1]);
			$end = ord($chars[2]);
			for ($i = $start; $i <= $end; $i++){
				$ret .= chr($i);
			}
			return $ret;
		}
	}

	/**
	 * The internal string storage.
	 * @var string
	 */
	protected $str = "";

	/**
	 * Length of the string.
	 * @var int
	 */
	protected $len = 0;

	/**
	 * Position of the iterator.
	 * @var int
	 */
	protected $current = 0;

	public function __construct($str = ""){
		$this->init($str);
	}

	public function __toString(){
		return $this->str;
	}

	public function init($str){
		$this->str = $str;
		$this->len = strlen($str);
	}

	public function goToNext($chars, $mask = "", $removeMask = true){
		$chars = self::parseCharCollection($chars);
		$text = "";
		for (; $this->valid(); $this->next()){
			$c = $this->current();
			if (strpos($chars, $c) !== false){
				break;
			}
			elseif ($c === $mask){
				$this->next();
				if ($this->valid()){
					if (!$removeMask){
						$text .= $mask;
					}
					$text .= $this->current();
				}
				else {
					$text .= $mask;
					break;
				}
			}
			else {
				$text .= $c;
			}
		}
		return $text;
	}

	public function goToNextNot($chars){
		$chars = self::parseCharCollection($chars);
		$text = "";
		for (; $this->valid(); $this->next()){
			$c = $this->current();
			if (strpos($chars, $c) === false){
				break;
			}
			else {
				$text .= $c;
			}
		}
		return $text;
	}

	public function offsetExists($offset){
		return $offset >= 0 && $offset < $this->len;
	}

	public function offsetGet($offset){
		return $this->str[$offset];
	}

	public function offsetSet($offset, $value){
		if (strlen($value) === 1){
			$this->str[$offset] = $value;
		}
		else {
			$this->init(substr($this->str, 0, $offset) . $value . substr($this->str, $offset + 1));
		}
	}

	public function offsetUnset($offset){
		$this->init(substr($this->str, 0, $offset) . substr($this->str, $offset + 1));
	}

	public function count(){
		return $this->len;
	}

	public function current(){
		return $this->str[$this->current];
	}

	public function key(){
		return $this->current;
	}

	public function next(){
		$this->current++;
	}

	public function prev(){
		$this->current--;
	}

	public function rewind(){
		$this->current = 0;
	}

	public function seek($position){
		if ($this->offsetExists($position)){
			$this->current = $position;
		}
		else {
			throw new OutOfBoundsException();
		}
	}

	public function valid(){
		return $this->offsetExists($this->current);
	}

	public function serialize(){
		return serialize($this->str);
	}

	public function unserialize($serialized){
		$this->init(unserialize($serialized));
	}

}

?>
