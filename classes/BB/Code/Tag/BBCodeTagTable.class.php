<?php
/**
 * BBCodeTagTable definition file.
 */

/**
 * BBCode for a table.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagTable extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("block", "inline", "element");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("class" => false);
	


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 * @todo implementation of table to html
	 */
	public function toHTML(){
		
	}
}

?>
