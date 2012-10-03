<?php
/**
 * BBCodeTagQuote definition file
 */

/**
 * Represention of a BBCode-tag [quote].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagQuote extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "block";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("inline");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("author" => false, "date" => false, "class" => false);

	/**
	 * {@inheritdoc}
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
