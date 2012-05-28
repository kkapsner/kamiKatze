<?php

/**
 * @author kkapsner
 */
class BBCodeTagImage extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array("text");
	protected static $allowedParents = array("block", "inline");
	
	/**
	 * The path to the "local" images directory.
	 * @var string
	 */
	public static $imageDirectory = "/images/";

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("url" => false, "alignment" => false, "width" => false, "height" => false, "legend" => false);
	

	/**
	 * Generates HTML.
	 * @return string
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
