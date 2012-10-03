<?php
/**
 * BBCodeTagPhpfunction definition file
 */

/**
 * Represention of a BBCode-tag [phpfunction].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 * @todo document usage
 */
class BBCodeTagPhpfunction extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("text");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("name" => false);

	/**
	 * {@inheritdoc}
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
