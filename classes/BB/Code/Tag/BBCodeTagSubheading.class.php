<?php
/**
 * BBCodeTagSubheading definition file
 */

/**
 * Represention of a BBCode-tag [subheading].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagSubheading extends BBCodeTagSimpleReplace{
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
	protected $htmlTag = "h4";

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
