<?php

/**
 * @author kkapsner
 */
class BBCodeTagTitle extends BBCodeTagSimpleReplace{
	protected static $type = "block";
	protected static $allowedChildren = array("inline");
	protected static $allowedParents = array("block", "inline");


	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = "h1";

	/**
	 * The class.
	 * @var string
	 */
	protected $class = "title";

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("class" => false, "id" => false);
}

?>
