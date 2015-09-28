<?php
/**
 * BBCodeTagText definition file
 */

/**
 * Represention of plain text.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagText extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array();

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("text" => "", "XML" => false);

	/**
	 * {@inheritdoc}
	 */
	public function toBBCode(){
		return $this->text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		return $this->encodeHTML($this->text);
	}

	/**
	 * Encodes to HTML: starting and ending \n and \r are removed, html-entities are set and the remaining \n and \r are replaced by <br>s.
	 * @param string $text
	 * @return string
	 */
	public function encodeHTML($text){
		$parent = $this->parent;
		do {
			$prev = $this->previousNode();
		} while ($prev instanceof self);
		do {
			$next = $this->nextNode();
		} while ($next instanceof self);
		if (
			(
				!$prev && 
				$this->parent &&
				$this->parent->getType() === "block"
			) ||
			(
				$prev &&
				$prev->getType() === "block"
			)
		){
			$text = preg_replace("/^[\\n\\r\\s]+/", "", $text);
		}
		if (
			(
				!$next &&
				$this->parent && 
				$this->parent->getType() === "block"
			) ||
			(
				$next &&
				$next->getType() === "block"
			)
		){
			$text = preg_replace("/[\\n\\r\\s]+$/", "", $text);
		}
		return nl2br(
			htmlentities(
				$text,
				ENT_QUOTES,
				$this->getCharset()
			)
		);
	}

	/**
	 * Like toHTML but only a substring is returned.
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	public function substringToHTML($start = 0, $length = 0){
		switch (func_num_args()){
			case 0:
				return $this->encodeHTML($this->text);
				break;
			case 1:
				return $this->encodeHTML(substr($this->text, $start));
				break;
			default:
				return $this->encodeHTML(substr($this->text, $start, $length));
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function toText(){
		return $this->text;
	}

	/**
	 * A text cannot contain other children! If another text is added this text is appended.
	 * @param Node $tag
	 * @return bool
	 */
	public function appendChild(Node $tag){
		if ($tag instanceof BBCodeTagText){
			$this->text .= $tag->text;
			return true;
		}
		else {
			return false;
		}
	}

}

?>
