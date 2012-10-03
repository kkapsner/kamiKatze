<?php
/**
 * VieableHTMLTag definition file
 */

/**
 * Viewable HTML tag.
 *
 * @author Korbinian Kapsner
 * @package Viewable\HTML\Tag
 */
class ViewableHTMLTag extends ViewableHTML{
	/**
	 * The tag name.
	 * @var string
	 */
	public $tagName = "";
	
	/**
	 * The content of the HTML tag.
	 * @var ViewableHTML
	 */
	public $content = null;

	/**
	 * Constructor of ViewableHTMLTag
	 * 
	 * @param string $tagName
	 */
	public function __construct($tagName = ""){
		$this->tagName = $tagName;
	}

}

?>
