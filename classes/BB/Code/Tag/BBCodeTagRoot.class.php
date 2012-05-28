<?php

/**
 * Description of BBCodeTagI
 *
 * @author kkapsner
 */
class BBCodeTagRoot extends BBCodeTag{
	protected static $type = "block";
	protected static $allowedChildren = array("block", "inline");
	protected static $allowedParents = array();
	
	protected $parameter = array();
	
	/**
	 *
	 * @var BBCodeParser
	 */
	public $parser = NULL;
	
	public function __construct(array $parameter = array()){
		parent::__construct($parameter);
		$this->tagName = NULL;
	}

	public function toBBCode(){
		return $this->childrenToBBCode();
	}

	public function toHTML(){
		return $this->childrenToHTML();
	}
}

?>
