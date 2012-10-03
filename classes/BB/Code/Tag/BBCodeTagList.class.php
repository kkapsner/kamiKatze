<?php
/**
 * BBCodeTagList definition file
 */

/**
 * Represention of a BBCode-tag [list].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 * @todo document usage
 */
class BBCodeTagList extends BBCodeTag{
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
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("type" => false, "start" => false, "class" => false);

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		$startHTML = "<";
		$endHTML = "</";
		$innerHTML = "";

		if ($this->type && strlen($this->type) === 1 && strpos("1aAiI", $this->type) !== false){
			$startHTML .= 'ol type="' . $this->type . '"';
			$endHTML .= "ol";
			if ($this->start){
				$startHTML .= ' start="' . htmlentities($this->start, ENT_QUOTES, $this->getCharset()) . '"';
			}
		}
		else {
			$startHTML .= "ul";
			$endHTML .= "ul";
		}
		if ($this->class){
			$startHTML .= ' class="' . htmlentities($this->class, ENT_QUOTES, $this->getCharset()) . '"';
		}

		$startHTML .= ">";
		$endHTML .= ">";

		foreach ($this as $child){
			if ($child instanceof BBCodeTagText){
				foreach (preg_split('\r\n|\n\|\r', $child->text) as $line){
					$innerHTML .= '</li><li>';
				}
			}
			else {
				$innerHTML .= $child->toHTML();
			}
		}
		$innerHTML = str_replace("<li></li>", "", "<li>" . $innerHTML . "</li>");
		
		if ($innerHTML === ""){
			return "";
		}

		return $startHTML . $innerHTML . $endHTML;
	}
}
?>
