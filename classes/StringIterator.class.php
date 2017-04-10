<?php
/**
 * StringIterator definition file
 */

/**
 * Iterator for a string
 *
 * @author Korbinian Kapsner
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

	/**
	 * Constructor of StringIterator
	 *
	 * @param string $str
	 */
	public function __construct($str = ""){
		$this->init($str);
	}

	/**
	 * Magic function __toString().
	 *
	 * @return string the string to iterate.
	 */
	public function __toString(){
		return $this->str;
	}

	/**
	 * Initialialises the iterator for the given string.
	 * 
	 * @param string $str the new string
	 */
	public function init($str){
		$this->str = $str;
		$this->len = strlen($str);
	}

	/**
	 * Iterates to the next char that is one of the provided chars.
	 *
	 * The char list can have ranges like "a-f". If the list should include the hyphen put it at the end.
	 *
	 * @param string $chars the chars to match.
	 * @param string $mask the char to mask the chars
	 * @param boolean $removeMask if the mask should be removed in output or not
	 * @return string the text found between the old and the new position
	 */
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

	/**
	 * Iterates to the next char that is not one of the proveded chars.
	 *
	 * The char list can have ranges like "a-f". If the list should include the hyphen put it at the end.
	 *
	 * @param string $chars
	 * @return string the text found between the old and the new position
	 */
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
	
	/**
	 * Checks if the string after the current position starts with a specific
	 * search string. If the string is found the iterator advances behind the
	 * matched string.
	 * 
	 * @param string $search The searched string.
	 * @param boolean $caseInsensitive If the search should be performed case
	 *     insensitive
	 * @return boolean If the search is present at the current position.
	 */
	public function isCurrentEqual($search, $caseInsensitive = false){
		$len = strlen($search);
		$testStr = substr($this->str, $this->current, $len);
		if ($caseInsensitive){
			$search = strtoupper($search);
			$testStr = strtoupper($testStr);
		}
		if ($testStr === $search){
			$this->current += $len;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $offset
	 * @return boolean
	 */
	public function offsetExists($offset){
		return $offset >= 0 && $offset < $this->len;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $offset
	 * @return string
	 */
	public function offsetGet($offset){
		return $this->str[$offset];
	}

	/**
	 * Removes the char at the given $offset and inserts the given value string.
	 *
	 * @param int $offset
	 * @param string $value
	 */
	public function offsetSet($offset, $value){
		if (strlen($value) === 1){
			$this->str[$offset] = $value;
		}
		else {
			$this->init(substr($this->str, 0, $offset) . $value . substr($this->str, $offset + 1));
		}
	}

	/**
	 * Removes the char a the given offset.
	 *
	 * @param int $offset
	 */
	public function offsetUnset($offset){
		$this->init(substr($this->str, 0, $offset) . substr($this->str, $offset + 1));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function count(){
		return $this->len;
	}

	/**
	 * Returns the current char.
	 *
	 * @return string
	 */
	public function current(){
		return $this->str[$this->current];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return int
	 */
	public function key(){
		return $this->current;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next(){
		$this->current++;
	}

	/**
	 * {@inheritdoc}
	 */
	public function prev(){
		$this->current--;
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind(){
		$this->current = 0;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param int $position
	 * @throws OutOfBoundsException
	 */
	public function seek($position){
		if ($this->offsetExists($position)){
			$this->current = $position;
		}
		else {
			throw new OutOfBoundsException();
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return boolean
	 */
	public function valid(){
		return $this->offsetExists($this->current);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function serialize(){
		return serialize($this->str);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param string $serialized
	 */
	public function unserialize($serialized){
		$this->init(unserialize($serialized));
	}

}

?>
