<?php

/**
 * @author kkapsner
 */
class BBCodeTagB extends BBCodeTagSimpleReplace{
	protected static $type = "inline";
	protected static $allowedChildren = array("inline");
	protected static $allowedParents = array("block", "inline");


	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = "b";

	/**
	 * The class.
	 * @var string
	 */
	protected $class = NULL;

	/**
	 * The parameter list.
	 * @var array
	 */
	#protected $parameter = array();
}

?>
