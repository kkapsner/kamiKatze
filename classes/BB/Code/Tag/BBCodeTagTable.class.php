<?php
/**
 * BBCodeTagTable definition file.
 */

/**
 * BBCode for a table.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 * @todo document usage
 */
class BBCodeTagTable extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("block", "inline", "element");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("class" => false);
	


	/**
	 * {@inheritdoc}
	 *
	 * @return string
	 */
	public function toHTML(){
		if ($this->count()){
			$ret = "<table";
			foreach ($this->parameter as $k => $v){
				if ($v){
					if ($v === true){
						$ret .= " " . $k;
					}
					else {
						$ret .= " " . $k . '="'  . htmlentities($v, ENT_QUOTES, $this->getCharset()) . '"';
					}
				}
			}
			$ret .= "><tr><td>";
			$len = $this->count() - 1;
			foreach ($this as $cidx => $child){
				if (is_a($child, "BBCodeTagText")){
					$text = $child->toBBCode();
					if ($cidx === 0){
						$text = preg_replace("/^\\s+/", "", $text);
					}
					if ($cidx === $len){
						$text = preg_replace("/\\s+$/", "", $child->toBBCode());
					}
					$lines = preg_split("/[\\r\\n]+/", $text);
					foreach ($lines as $i => $line){
						if ($i){
							$ret .= "</td></tr><tr><td>";
						}
						$cells = preg_split("/(?:\\t+|\\s{2,}|\\|)/", $line);
						foreach ($cells as $j => $cell){
							if ($j){
								$ret .= "</td><td>";
							}
							$ret .= $child->encodeHTML($cell);
							
						}
					}
				}
				else {
					$ret .= $child->toHTML();
				}
			}
			$ret.= "</td></tr></table>";
			return $ret;
		}
		else {
			return "";
		}
		
	}
}

?>
