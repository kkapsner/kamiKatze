<?php
/**
 * BBCodeTagElement definition file
 */

/**
 * Representation of the BBCode-tag [element]
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagElement extends BBCodeTag{

	/**
	 * {@inheritdoc}
	 */
	protected static $type = "";

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
	protected $parameter = array();

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		return $this->childrenToHTML();
	}
}

?>
