<?php

/**
 * @author kkapsner
 */
class BBCodeTagSubtitle extends BBCodeTagSimpleReplace{
	protected static $allowedChildren = array("inline");
	protected static $allowedParents = array("block", "inline");


	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = "h2";

	/**
	 * The class.
	 * @var string
	 */
	protected $class = NULL;

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("class" => false, "id" => false);
}

?>