<?php
/**
 * BBCodeTagIf definition file
 */

/**
 * Represention of a BBCode-tag [if].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagIf extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("condition", "boolean");
	
	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("inline", "block");

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
		return $this->getResolvedTag()->childrenToHTML();
	}
	
	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toText(){
		return $this->getResolvedTag()->childrenToHTML();
	}
	
	/**
	 * Resolves the comparision and returns the matching node.
	 * 
	 * @return BBCodeTag
	 */
	public function getResolvedTag(){
		$cond = null;
		$true = null;
		$false = null;
		foreach ($this as $child){
			if ($child instanceof BBCodeTagCondition){
				$cond = $child;
			}
			if ($child instanceof BBCodeTagTrue){
				$true = $child;
			}
			if ($child instanceof BBCodeTagFalse){
				$false = $child;
			}
		}
		$return = null;
		if ($cond !== null){
			$return = $cond->exec()? $true: $false;
		}
		if ($return === null){
			return new BBCodeTagText(array("text" => ""));
		}
		return $return;
	}
}