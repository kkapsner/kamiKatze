<?php
/**
 * BBCodeTagLink definition file
 */

/**
 * Represention of a BBCode-tag [link].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagLink extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("inline", "!link");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("url" => false, "type" => false);
	
	/**
	 * The type-parser.
	 * @var array
	 */
	protected static $typeProperties = array(
		"mail" => array("mailto:{url}", false, false),
		"extern" => array(array("BBCodeTagLink", "parseExternURL"), true, false),
	);
	
	/**
	 * Registers a type with a template or the appropriate parsing-callback.
	 * Template: $parse is a string - within this string the sequence "{url}" is replaced by the url
	 * Callback: $parse is an array representing a valid callback. $parse takes the url as parameter and returns the new url.
	 * 
	 * @param string $type
	 * @param mixed $parse 
	 * @param bool $newWindow if the link should be opened in a new window/tab.
	 * @param callback $innerHTMLCallback callback to change the inner HTML.
	 */
	public static function registerType($type, $parse, $newWindow = false, $innerHTMLCallback = false){
		self::$typeProperties[$type] = array($parse, $newWindow, $innerHTMLCallback);
	}

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		$startHTML = '<a class="link ' . $this->type . '"';
		$innerHTML = $this->childrenToHTML();
		$endHTML = "</a>";

		$url = $this->url? $this->url: $this->childrenToText();
		if ($this->type && array_key_exists($this->type, self::$typeProperties)){
			$parse = self::$typeProperties[$this->type];
			if (is_string($parse[0])){
				$url = str_replace("{url}", $url, $parse[0]);
			}
			elseif (is_array($parse[0])){
				$url = call_user_func($parse[0], $url);
			}
			
			if ($parse[1]){
				$startHTML .= ' target="_blank"';
			}

			if ($parse[2]){
				$innerHTML = call_user_func($parse[2], $innerHTML);
			}
		}
		$startHTML .= ' href="' . htmlentities($url, ENT_QUOTES, $this->getCharset()) . '">';
		return $startHTML . $innerHTML . $endHTML;
	}

	/**
	 * Modifies an URL so that it is an external URL afterwards
	 * @param string $url
	 * @return string
	 */
	private function parseExternURL($url){
		if (!preg_match('@^(?:https?|ftp)://@i', $url)){
			$url = "http://" . $url;
		}
		return $url;
	}
}

?>
