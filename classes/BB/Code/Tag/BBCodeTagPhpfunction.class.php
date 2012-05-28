<?php

/**
 * @author kkapsner
 */
class BBCodeTagPhpfunction extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array("text");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("name" => false);
	

	/**
	 * Generates HTML.
	 * @return string
	 */
	public function toHTML(){
		if ($this->name){
			if (array_key_exists($this->name, self::$registeredFunctions)){
				return call_user_func(
					self::$registeredFunctions[$this->name],
					$this->childrenToText()
				);
			}
		}
	}

	/**
	 * The registered functions.
	 * @var array
	 */
	protected static $registeredFunctions = array();

	/**
	 * Registers a function callback for use in a [phpFunction]-tag.
	 * @param callback $func
	 * @param string $name
	 */
	public static function register($func, $name = null){
		if ($name === null){
			if (is_array($func)){
				$name = $func[1];
			}
			else {
				$name = $func;
			}
		}
		self::$registeredFunctions[$name] = $func;
	}
}

?>
