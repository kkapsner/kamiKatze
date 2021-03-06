<?php
/**
 * BBCodeTagImage definition file
 */

/**
 * Represention of a BBCode-tag [image].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagImage extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

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
	public static $imageDirectory = "/images/";

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("url" => false, "alignment" => false, "width" => false, "height" => false, "legend" => false);

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		$url = $this->url;
		if (!preg_match("/^http/", $this->url)){
			$url = self::$imageDirectory . $url;
		}
		$class = "";
		switch ($this->alignment){
			case "right":
			case "rechts":
				$class = " rightFloat";
				break;
			case "left":
			case "links":
				$class = " leftFloat";
				break;
		}
		$parameter = "";
		if ($this->width){
			$parameter = ' width="' . str_replace("px", "", $this->width) . '"';
		}
		if ($this->height){
			$parameter .= ' height="' . str_replace("px", "", $this->height) . '"';
		}
		$parameter .= ' class="contentImage' . $class . '"';
		if ($this->legend){
			$parameter .= ' title="' . htmlentities($this->legend, ENT_QUOTES, $this->getCharset()) . '"';
		}
		return '<img src="' . $url . '" alt="' . $this->childrenToHTML() . '"' . $parameter . '>';
	}
}

?>
