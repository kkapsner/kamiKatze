<?php
/**
 * BBCodeTagI definition file
 */

/**
 * Represention of a BBCode-tag [i].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagI extends BBCodeTagSimpleReplace{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $htmlTag = "i";

	/**
	 * {@inheritdoc}
	 */
	protected $class = NULL;
}

?>
