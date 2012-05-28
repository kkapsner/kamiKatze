<?php

/**
 * Description of ViewableHTMLNavigation
 *
 * @author kkapsner
 */
class ViewableHTMLNavigation extends ViewableHTML{
	/**
	 *
	 * @var array
	 */
	protected $items;

	/**
	 *
	 * @return bool
	 */
	public function hasItems(){
		return count($this->items) != 0;
	}

	/**
	 *
	 * @param mixed $item
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
