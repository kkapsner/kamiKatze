<?php

/**
 * @author kkapsner
 */
class BBCodeTagElement extends BBCodeTag{
	protected static $type = "";
	protected static $allowedChildren = array("block", "inline");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array();
	

	/**
	 * Generates HTML.
	 * @return string
	 */
	abstract public function toHTML(){
		return $this->childrenToHTML();
	}
}

?>
