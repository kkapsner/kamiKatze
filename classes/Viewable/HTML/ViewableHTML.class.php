<?php

/**
 * Description of ViewableHTML
 *
 * @author kkapsner
 */
class ViewableHTML extends ViewableImplementation{
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
	 *
	 * @return array
	 */
	public function getHTMLAttirbuteList(){
		return array_keys($this->attributes);
	}

	/**
	 *
	 * @param string $name
	 * @param string $value
	 * @param bool $isUrl
	 */
	public function setHTMLAttribute($name, $value, $isUrl = false){
		$this->attributes[$name] = (object) array(
			#"name" => $name,
			"value" => $value,
			"isUrl" => $isUrl
		);
	}

	/**
	 *
	 * @param string $name
	 * @param bool $html
	 * @return string
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
	 *
	 * @param bool $html
	 * @return mixed 
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

	public function html($text){
		return htmlentities($text, ENT_QUOTES, $this->charset);
	}
	public function url($text){
		return str_replace(" ", "%20", $this->html($text));
	}
}

?>
