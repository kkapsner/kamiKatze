<?php
/**
 * BBCodeTagP definition file
 */

/**
 * Represention of a BBCode-tag [p].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagP extends BBCodeTagSimpleReplace{
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
	protected $htmlTag = "p";

	/**
	 * {@inheritdoc}
	 */
	protected $class = NULL;
}

?>
