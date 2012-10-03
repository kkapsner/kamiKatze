<?php
/**
 * BBCodeTagMap definition file
 */

/**
 * Represention of a BBCode-tag [map].
 *
 * @author Korbinian Kapsner
 * @package BB\Code\Tag
 */
class BBCodeTagMap extends BBCodeTag{
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
	protected $parameter = array("zoom" => 14, "width" => false, "height" => false);

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		return '<iframe ' .
			'src="http://maps.google.de/maps?' .
				'z=' . $this->zoom . '&amp;' .
				'q=' . urlencode($this->childrenToText()) . '&amp;' .
				'output=embed"' .
			($this->width? ' width="' . $this->width . '"': "") .
			($this->height? ' height="' . $this->height . '"': "") .
			'></iframe>';
	}
}

?>
