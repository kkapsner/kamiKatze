<?php
/**
 * BBCodeTagSection definition file
 */

/**
 * Represention of a BBCode-tag [section].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagSection extends BBCodeTagSimpleReplace{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "block";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("inline", "block");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $htmlTag = "section";

	/**
	 * {@inheritdoc}
	 */
	protected $class = NULL;
}

?>
