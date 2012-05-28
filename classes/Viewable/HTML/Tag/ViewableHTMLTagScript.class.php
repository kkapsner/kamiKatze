<?php
/**
 * Description of ViewableHTMLTagScript
 *
 * @author kkapsner
 */
class ViewableHTMLTagScript extends ViewableHTMLTag{
	public $tagName = "script";
	/**
	 *
	 * @var string
	 */
	public $code = "";

	public function __construct($code = ""){
		$this->setHTMLAttribute("type", "text/javascript");
		$this->code = $code;
	}
}

?>
