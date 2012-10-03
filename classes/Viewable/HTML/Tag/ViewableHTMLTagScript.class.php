<?php
/**
 * ViewableHTMLTagScript definition file
 */

/**
 * Viewable <script> tag
 *
 * @author Korbinian Kapsner
 * @package Viewable\HTML\Tag
 */
class ViewableHTMLTagScript extends ViewableHTMLTag{
	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	public $tagName = "script";

	/**
	 * The code in the <script> tag.
	 *
	 * @var string
	 */
	public $code = "";

	/**
	 * Constructor of ViewableHTMlTagScript
	 *
	 * @param string $code
	 */
	public function __construct($code = ""){
		$this->setHTMLAttribute("type", "text/javascript");
		$this->code = $code;
	}
}

?>
