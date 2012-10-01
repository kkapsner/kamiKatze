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
	 * @todo implementation of table to html
	 */
	public function toHTML(){
		
	}
}

?>
