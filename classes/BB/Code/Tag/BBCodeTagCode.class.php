<?php

/**
 * @author kkapsner
 */
class BBCodeTagCode extends BBCodeTag{
	protected static $type = "block";
	protected static $allowedChildren = array("text");
	protected static $allowedParents = array("block", "inline");


	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("language" => false);


	/**
	 * Generates HTML.
	 * @return string
	 */
	public function toHTML(){
		$add = ":";
		if ($this->language){
			$add = ' (' . $this->language . '):';
			if (strToLower($this->language) == "js"){
				$add .=	'<span class="buttons">' .
							'<span class="button" onclick="eval(this.parentNode.parentNode.getElementsByTagName(\'code\')[0].innerHTML.decodeHTMLentities());">ausf&uuml;hren</span>' .
							'<span class="button" onclick="var n = this.parentNode.parentNode.getElementsByTagName(\'code\')[0]; n.contentEditable = true; n.focus();">&auml;ndern</span>' .
						'</span>';
			}
		}
		return '<div class="code">Code' . $add . '<code>' . str_replace("\t", "    ",$this->childrenToText()) . '</code></div>';
	}
}

?>
