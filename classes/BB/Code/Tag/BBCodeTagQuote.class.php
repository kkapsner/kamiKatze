<?php

/**
 * @author kkapsner
 */
class BBCodeTagQuote extends BBCodeTag{
	protected static $type = "block";
	protected static $allowedChildren = array("inline");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("author" => false, "date" => false, "class" => false);
	

	/**
	 * Generates HTML.
	 * @return string
	 */
	public function toHTML(){
		$author = "";
		if ($this->author){
			$date = "";
			if ($this->date){
				$date = ' <span class="datum">(' . $this->date . ')</span>';
			}
			$author = '<cite class="author">- ' . $this->author . $date . ' -</cite>';
		}
		return '<blockquote class="' . $this->class . '">&ldquo;' . $this->childrenToHTML() . "&rdquo;" . $author . '</blockquote>';
	}
}

?>
