<?php
/**
 * BBCodeTagB definition file.
 */

/**
 * Represention of a b-tag. The text within this node is bold.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagB extends BBCodeTagSimpleReplace{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("inline");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline", "");



	/**
	 * {@inheritdoc}
	 */
	protected $htmlTag = "b";


	/**
	 * {@inheritdoc}
	 */
	protected $class = NULL;
}

?>
