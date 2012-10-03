<?php
/**
 * BBCodeTagHeading definition file
 */

/**
 * Representation of the BBCode-tag [heading]
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagHeading extends BBCodeTagSimpleReplace{
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
	protected $htmlTag = "h3";

	/**
	 * {@inheritdoc}
	 */
	protected $class = NULL;

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("class" => false, "id" => false);
}

?>
