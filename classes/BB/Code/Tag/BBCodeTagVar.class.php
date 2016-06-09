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
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 * @todo describe parameter
	 */
	protected $parameter = array(
		"viewContext" => "",
		"format" => "%s",
		"viewArgument" => ""
	);
	


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toHTML(){
		$vars = $this->getRoot()->variables;
		$name = $this->childrenToText();
		if (is_array($vars) && array_key_exists($name, $vars)){
			$obj = $vars[$name];
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
				return $this->html(sprintf($this->format, $obj));
			}
		}
		else {
			return $this->html($name);
		}
	}
}