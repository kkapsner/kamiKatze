<?php
/**
 * BBCodeTagCode definition file
 */

/**
 * Representation of the BBCode-tag [code]
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagCode extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "block";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("text");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("language" => false);

	/**
	 * {@inheritdoc}
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
