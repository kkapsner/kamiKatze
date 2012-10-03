<?php
/**
 * ViewableHTML definition file
 */

/**
 * Abstract class for any HTML related view
 *
 * @author Korbinian Kapsner
 * @package Viewable\HTML
 */
abstract class ViewableHTML extends ViewableImplementation{
	/**
	 * Charset used to encode strings.
	 * @var string
	 */
	public $charset = "UTF-8";

	/**
	 * The HTML-attributes of the representation
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Returns an array of the registered HTML attirbutes
	 * @return string[]
	 */
	public function getHTMLAttirbuteList(){
		return array_keys($this->attributes);
	}

	/**
	 * Sets a HTML attribute.
	 *
	 * @param string $name the attribute name
	 * @param string $value the attribute value
	 * @param bool $isUrl if the attribute value is an URL
	 */
	public function setHTMLAttribute($name, $value, $isUrl = false){
		$this->attributes[$name] = (object) array(
			#"name" => $name,
			"value" => $value,
			"isUrl" => $isUrl
		);
	}

	/**
	 * Gets the value of a HTMl attribute
	 *
	 * @param string $name the attribute name
	 * @param bool $html if the HTML representation should be returned
	 * @return string the attribute value or the HTML representation of the attribute (i.e. value="key"). If the attribute is not
	 * registered an empty string is returned.
	 */
	public function getHTMLAttribute($name, $html = false){
		if ($html){
			if (array_key_exists($name, $this->attributes)){
				$att = $this->attributes[$name];
				return " " . $this->html($name) .
					'="' . 
					(
						$att->isUrl?
							$this->url($att->value):
							$this->html($att->value)
					) .
					'"';
			}
			else {
				return "";
			}
		}
		else {
			if (array_key_exists($name, $this->attributes)){
				return $this->attributes[$name];
			}
			else {
				return "";
			}
		}
	}

	/**
	 * Returns all HTML attributes
	 *
	 * @param bool $html if the HTML representaiton should be returned
	 * @return mixed an array of all attributes (the names are the keys in the array) or a string with all HTML representations of all attributes
	 */
	public function getAllHTMLAttributes($html = false){
		$ret = $html? "": array();
		foreach ($this->attributes as $name => $att){
			if ($html){
				$ret .= $this->getHTMLAttribute($name, $html);
			}
			else {
				$ret[$name] = $this->getAttribute($name, $html);
			}
		}
		return $ret;
	}

	/**
	 * Escapes the text for save HTML output
	 *
	 * @param string $text
	 * @return string
	 */
	public function html($text){
		return htmlentities($text, ENT_QUOTES, $this->charset);
	}

	/**
	 * Escapes the text for a save URL output in HTML
	 * 
	 * @param string $text
	 * @return string
	 */
	public function url($text){
		return str_replace(" ", "%20", $this->html($text));
	}
}

?>
