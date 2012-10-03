<?php
/**
 * BBCodeTagTitle definition file
 */

/**
 * Represention of a BBCode-tag [title].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagTitle extends BBCodeTagSimpleReplace{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "block";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("inline");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $htmlTag = "h1";

	/**
	 * {@inheritdoc}
	 */
	protected $class = "title";

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("class" => false, "id" => false);
}

?>
