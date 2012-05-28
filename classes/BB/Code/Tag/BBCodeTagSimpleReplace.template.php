<?php

/**
 * @author kkapsner
 */
class BBCodeTag<tagName> extends BBCodeTagSimpleReplace{
	protected static $type = "block";
	protected static $allowedChildren = array("block", "inline");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = NULL;

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
