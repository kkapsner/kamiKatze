<?php
/**
 * ViewableHTMlNavigationItem definition file
 */

/**
 * Representation of a navigation item
 *
 * @author Korbinian Kapsner
 * @package Viewable\HTML\Navigation
 */

class ViewableHTMLNavigationItem extends ViewableHTML{
	/**
	 * The sub navigation (if one is present) or null
	 * @var ViewableHTMLNavigation
	 */
	protected $subNavigation = null;

	/**
	 * The text to display
	 * @var string
	 */
	public $text = "";
	
	/**
	 * If HTML should be displayed it can be put here.
	 * @var string
	 */
	public $innerHTML = null;

	/**
	 * The link URL.
	 * @var string
	 */
	public $url = "";

	/**
	 * If the item points to the currently active site.
	 * @var boolean
	 */
	public $active = false;

	/**
	 * If the link points to an external URL
	 * @var boolean
	 */
	public $extern = false;

	/**
	 * Constructor of ViewableHTMLnavigationItem
	 *
	 * @param string $text
	 * @param string $url
	 */
	public function __construct($text, $url){
		$this->text = $text;
		$this->url = $url;
	}

	/**
	 * Adds a sub navigation to the item. Only one subnavigation is possible on one item.
	 * 
	 * @param bool $forceNew if a new navigation instance should be forced
	 * @return ViewableHTMLNavigation the subnavigation
	 */
	public function addNavigation($forceNew = false){
		if ($this->subNavigation === null || $forceNew){
			$this->subNavigation = new ViewableHTMLNavigation();
			$this->subNavigation->charset = $this->charset;
		}
		return $this->subNavigation;
	}
}

?>
