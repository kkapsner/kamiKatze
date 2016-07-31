<?php
/**
 * BBCodeTagVar definition file
 */

/**
 * Represention of a BBCode-tag [var].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagVar extends BBCodeTag{
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
	protected static $allowedParents = true;

	/**
	 * {@inheritdoc}
	 * @todo describe parameter
	 */
	protected $parameter = array(
		"viewContext" => "",
		"format" => "%s",
		"viewArgument" => "",
		"required" => false,
		"optional" => false
	);
	


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toText(){
		$vars = $this->getRoot()->variables;
		$name = $this->childrenToText();
		$obj = $this->getValue($name, $vars);
		if ($obj === null){
			$event = new Event("variableNotFound", $this);
			$event->setProperty("variable", $name);
			$this->emit($event);
			
			if ($this->required){
				throw new BBCodeError("Required variable " . $name . " missing.");
			}
			else if ($this->optional){
				$obj = "";
			}
			else {
				$obj = $name;
			}
		}
		if ($obj instanceof Viewable){
			$argName = $this->viewArgument;
			if ($argName && array_key_exists($argName, $vars)){
				$arg = $vars[$argName];
			}
			else {
				$arg = null;
			}
			return $obj->view($this->viewContext, false, $arg);
		}
		else {
			return sprintf($this->format, $obj);
		}
	}


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toHTML(){
		$vars = $this->getRoot()->variables;
		$name = $this->childrenToText();
		$obj = $this->getValue($name, $vars);
		if ($obj === null){
			$event = new Event("variableNotFound", $this);
			$event->setProperty("variable", $name);
			$this->emit($event);
			
			if ($this->required){
				throw new BBCodeError("Required variable " . $name . " missing.");
			}
			else if ($this->optional){
				$obj = "";
			}
			else {
				$obj = $name;
			}
		}
		if ($obj instanceof Viewable){
			$argName = $this->viewArgument;
			if ($argName && array_key_exists($argName, $vars)){
				$arg = $vars[$argName];
			}
			else {
				$arg = null;
			}
			return $obj->view($this->viewContext, false, $arg);
		}
		else {
			return htmlentities(sprintf($this->format, $obj), ENT_QUOTES, $this->getCharset());
		}
	}
	
	/**
	 * Tries to retrieve a value from an array by key of key chain.
	 * 
	 * E.g.: Given array("value" => 1, "subselect" => array("value" => 2)) the
	 *   key "value" would return 1 and the key "subselect.value" would return 2
	 * 
	 * @param type $name
	 * @param type $array
	 * @return mixed The value of the name in the array or null if not found.
	 */
	protected function getValue($name, $array){
		if (is_array($array)){
			if (array_key_exists($name, $array)){
				return $array[$name];
			}
			$dotPos = strpos($name, ".");
			if ($dotPos !== false){
				$rest = substr($name, $dotPos + 1);
				$name = substr($name, 0, $dotPos);
				if (array_key_exists($name, $array)){
					return $this->getValue($rest, $array[$name]);
				}
			}
		}
		return null;
	}
}