<?php

/*
 *
 *
 */

/**
 * Description of ViewableHTMLNode
 *
 * @author kkapsner
 */
class ViewableHTMLTag extends ViewableHTML{
	/**
	 *
	 * @var string
	 */
	public $tagName = "";
	/**
	 *
	 * @var ViewableHTML
	 */
	public $content = null;

	public function __construct($tagName = ""){
		$this->tagName = $tagName;
	}

}

?>
