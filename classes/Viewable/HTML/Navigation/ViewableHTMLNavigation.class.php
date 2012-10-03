<?php
/**
 * ViewableHTMLNavigation definition file
 */

/**
 * Viewable HTML navigation.
 *
 * @author Korbinian Kapsner
 * @package Viewable\HTML\Navigation
 */
class ViewableHTMLNavigation extends ViewableHTML{
	/**
	 * The navigation items.
	 * @var array
	 */
	protected $items;

	/**
	 * Checks if the navigation has items.
	 *
	 * @return boolean
	 */
	public function hasItems(){
		return count($this->items) != 0;
	}

	/**
	 * Adds an item to the navigation
	 * 
	 * @param mixed $item If $item is a ViewableHTMLNavigationItem it is simply added. If not $item and $url are used to call the constructor
	 * of ViewableHTMLNavigationItem and this item is added.
	 * @param string $url
	 * @return ViewableHTMLNavigationItem
	 */
	public function addItem($item, $url = ""){
		if (!is_a($item, "ViewableHTMLNavigationItem")){
			$item = new ViewableHTMLNavigationItem($item, $url);
			$item->charset = $this->charset;
		}
		$this->items[] = $item;
		return $item;
	}
}

?>
