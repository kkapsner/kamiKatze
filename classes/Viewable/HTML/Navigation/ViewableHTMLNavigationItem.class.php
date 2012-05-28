<?php

/**
 * Description of ViewableHTMLNavigationItem
 *
 * @author kkapsner
 */

class ViewableHTMLNavigationItem extends ViewableHTML{
	/**
	 *
	 * @var ViewableHTMLNavigation
	 */
	protected $subNavigation = null;
	/**
	 *
	 * @var string
	 */
	public $text = "";
	/**
	 * @var string
	 */
	public $innerHTML = null;
	/**
	 *
	 * @var string
	 */
	public $url = "";
	/**
	 *
	 * @var bool
	 */
	public $active = false;
	/**
	 *
	 * @var bool
	 */
	public $extern = false;

	/**
	 *
	 * @param string $text
	 * @param string $url
	 */
	public function __construct($text, $url){
		$this->text = $text;
		$this->url = $url;
	}

	/**
	 *
	 * @param bool $forceNew
	 * @return ViewableHTMLNavigation
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
