<?php

/**
 * @author kkapsner
 */
class BBCodeTag<tagName> extends BBCodeTag{
	protected static $type = "block";
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
	public function toHTML(){
		//TODO: <tagName> toHTML
	}
}

?>
