<?php
/**
 * ViewableHTMLTagStype definition file
 */

/**
 * Viewable style node.
 *
 * @author Korbinian kapsner
 * @package Viewable\HTML\Tag
 */
class ViewableHTMLTagStyle extends ViewableHTMLTag{
	/**
	 * {@inheritdoc}
	 * @var string
	 */
	public $tagName = "style";

	/**
	 * The CSS-style code
	 * @var string
	 */
	public $style = "";

	/**
	 * If the style is only applied to IE.
	 * @var bool
	 */
	public $ie = false;

	/**
	 * Constructor of ViewableHTMlTagStyle
	 */
	public function __construct(){
		$this->setHTMLAttribute("type", "text/css");
	}
}

?>
