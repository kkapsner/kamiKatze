<?php

/**
 * @author kkapsner
 */
class BBCodeTagTable extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array("block", "inline", "element");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("class" => false);
	

	/**
	 * Generates HTML.
	 * @return string
	 */
	abstract public function toHTML(){
		//TODO: Table toHTML
	}
}

?>
