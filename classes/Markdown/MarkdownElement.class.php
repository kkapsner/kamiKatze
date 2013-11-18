<?php

/**
 * MarkdownElement definition file
 */

/**
 * Description of MarkdownElement
 *
 * @author kkapsner
 */
abstract class MarkdownElement extends ViewableHTML{
	/**
	 * the next line
	 * @var MarkdownLine
	 */
	protected $next = null;
	
	/**
	 * the previous line
	 * @var MarkdownLine
	 */
	protected $prev = null;
	
	/**
	 * Constructor for a markdown element
	 * @param MarkdownElement $prevElement
	 */
	public function __construct($prevElement){
		$this->prev = $prevElement;
		if ($this->prev){
			$this->prev->next = $this;
		}
	}
}

?>
