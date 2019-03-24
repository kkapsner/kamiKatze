<?php
/**
 * BBCodeTagYoutube definition file.
 */

/**
 * Representation of a BBCode-tag [youtube]. This allows to include a youtube video in the code.
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagYoutube extends BBCodeTag{
	/**
	 * {@inheritdoc}
	 */
	protected static $type = "inline";

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedChildren = array("text");

	/**
	 * {@inheritdoc}
	 */
	protected static $allowedParents = array("block", "inline");

	/**
	 * {@inheritdoc}
	 */
	protected $parameter = array("border" => 0, "width" => 445, "height" => 364);

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		return '<iframe class="youTube" frameborder="' . $this->border . '" scrolling="no" marginheight="0" marginwidth="0" width="' . $this->width . '" height="' . $this->height . '" type="text/html" src="https://www.youtube.com/embed/' . $this->childrenToText() . '?autoplay=0&fs=0&iv_load_policy=3&showinfo=0&rel=0&cc_load_policy=0&start=0&end=0"></iframe>';
	}
}

?>
