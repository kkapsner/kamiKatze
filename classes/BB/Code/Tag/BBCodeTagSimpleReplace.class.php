<?php

/**
 * @author kkapsner
 */
abstract class BBCodeTagSimpleReplace extends BBCodeTag{
	/**
	 * The replace HTML tag.
	 * @var string
	 */
	protected $htmlTag = NULL;

	/**
	 * The class.
	 * @var string
	 */
	protected $class = NULL;

	protected $parameter = array("class" => false);

	public function toHTML(){
		$class = "";
		if ($this->class){
			$class = $this->class;
		}
		if (array_key_exists("class", $this->parameter)){
			$class .= " " . $this->parameter["class"];
		}
		$class = trim($class);
		$ret = "<" . $this->htmlTag;
		if ($class !== ""){
			$ret .= ' class="' . $class . '"';
		}
		foreach ($this->parameter as $k => $v){
			if ($k === "class"){
				continue;
			}
			if ($v){
				if ($v === true){
					$ret .= " " . $k;
				}
				else {
					$ret .= " " . $k . '="'  . htmlentities($v, ENT_QUOTES, $this->getCharset()) . '"';
				}
			}
		}
		if ($this->count() === 0){
			$ret .= " />";
		}
		else {
			$ret .= ">" . $this->childrenToHTML() . "</" . $this->htmlTag . ">";
		}
		return $ret;
	}
}

?>
