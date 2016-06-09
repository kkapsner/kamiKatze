<?php
/**
 * BBCodeTagRoot definition file
 */

/**
 * Represention of a BBCode-tree root.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagRoot extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "block";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array();

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array();
	
	/**
	 * The parse who creates the BBCode-Tree.
	 * @var BBCodeParser
	 */
	public $parser = NULL;

	/**
	 * The variables that are available for the nodes.
	 * @var mixed[]
	 */
	public $variables = NULL;

	/**
	 * {@inheritdoc}
	 *
	 * @param array $parameter
	 */
	public function __construct(array $parameter = array()){
		parent::__construct($parameter);
		$this->tagName = NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toBBCode(){
		return $this->childrenToBBCode();
	}

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		return $this->childrenToHTML();
	}
}