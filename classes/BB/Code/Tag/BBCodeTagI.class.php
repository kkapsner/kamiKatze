<?php

/**
 * @author kkapsner
 */
class BBCodeTagI extends BBCodeTagSimpleReplace{
	protected static $type = "inline";
	protected static $allowedChildren = array("block", "inline");
	protected static $allowedParents = array("block", "inline");


	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = "i";

	/**
	 * The class.
	 * @var string
	 */
	protected $class = NULL;

	/**
	 * The parameter list.
	 * @var array
	 */
	#protected $parameter = array("class" => false);
}

?>
