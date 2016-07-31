<?php
/**
 * BBCodeTagCondition definition file
 */

/**
 * Represention of a conditional BBCode-tag.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
abstract class BBCodeTagCondition extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "condition";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("condition", "text", "var");
	
	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("if", "condition");
	
	/**
	 * Executes the condition and returns the result.
	 * 
	 * @return Boolean The outcome of the condition.
	 */
	abstract public function exec();
	

	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toHTML(){
		return "";
	}
}