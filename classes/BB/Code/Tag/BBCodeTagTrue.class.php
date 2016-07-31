<?php
/**
 * BBCodeTagTrue definition file
 */

/**
 * Represention of a BBCode-tag [true].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagTrue extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "boolean";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("text", "inline", "var");
	
	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("if");

	/**
	 * {@inheritdoc}
	 * @todo describe parameter
	 */
	protected $parameter = array();
	


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toHTML(){
		return $this->childrenToHTML();
	}
}