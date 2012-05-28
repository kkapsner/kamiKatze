<?php

/**
 * 
 *
 * @author kkapsner
 */
class BBCodeTagText extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array();
	protected $parameter = array("text" => "", "XML" => false);

	public function toBBCode(){
		return $this->text;
	}

	public function toHTML(){
		return $this->encodeHTML($this->text);
	}

	/**
	 * Encodes to HTML: starting and ending \n and \r are removed, html-entities are set and the remaining \n and \r are replaced by <br>s.
	 * @param string $text
	 * @return string
	 */
	public function encodeHTML($text){
		return nl2br(
			htmlentities(
				trim(
					$text,
					"\n\r"
				),
				ENT_QUOTES,
				$this->getCharset()
			),
			$this->XML
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
			case 2:
				return $this->encodeHTML(substr($this->text, $start, $length));
				break;
		}
	}

	public function toText(){
		return $this->text;
	}

	/**
	 * A text cannot contain other children! If another text is added this text is appended.
	 * @param BBCodeTag $tag
	 * @return false
	 */
	public function appendChild(Node $tag){
		if ($tag instanceof BBCodeTagText){
			$this->text .= $tag->text;
		}
		return false;
	}

}

?>
