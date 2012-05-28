<?php

/**
 * Description of ViewableHTMLTagStyle
 *
 * @author kkapsner
 */
class ViewableHTMLTagStyle extends ViewableHTMLTag{
	public $tagName = "style";

	/**
	 *
	 * @var string
	 */
	public $style = "";

	/**
	 *
	 * @var bool
	 */
	public $ie = false;

	public function __construct(){
		$this->setHTMLAttribute("type", "text/css");
	}
}

?>
