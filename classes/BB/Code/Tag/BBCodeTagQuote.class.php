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
	protected $parameter = array("author" => false, "date" => false, "class" => false, "quotes" => "en");

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
		
		$quotes = array(
			"none" => array("", ""),
			"en" => array("&ldquo;", "&rdquo;"),
			"de" => array("&#8222;", "&#8220;"),
			"fr" => array("&#171;", "&#187;")
		);
		if (array_key_exists($this->quotes, $quotes)){
			$selectedQuotes = $quotes[$this->quotes];
		}
		else {
			$selectedQuotes = $quotes["en"];
		}
		
		return '<blockquote class="' . $this->class . '">' . $selectedQuotes[0] . $this->childrenToHTML() . $selectedQuotes[1] . $author . '</blockquote>';
	}
}

?>
