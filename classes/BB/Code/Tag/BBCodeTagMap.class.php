<?php

/**
 * @author kkapsner
 */
class BBCodeTagMap extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array("text");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("zoom" => 14, "width" => false, "height" => false);
	

	/**
	 * Generates HTML.
	 * @return string
	 */
	public function toHTML(){
		return '<iframe ' .
			'src="http://maps.google.de/maps?' .
				'z=' . $this->zoom . '&amp;' .
				'q=' . urlencode($this->childrenToText()) . '&amp;' .
				'output=embed"' .
			($this->width? ' width="' . $this->width . '"': "") .
			($this->height? ' height="' . $this->height . '"': "") .
			'></iframe>';
	}
}

?>
