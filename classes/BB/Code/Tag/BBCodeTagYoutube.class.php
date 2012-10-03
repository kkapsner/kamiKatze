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
	protected $parameter = array("border" => 0);

	/**
	 * {@inheritdoc}
	 */
	public function toHTML(){
		$url = 'http://www.youtube-nocookie.com/v/' . $this->childrenToText() . '&amp;hl=de_DE&amp;fs=1&amp;rel=0&amp;border=' . $this->rand;
		return '<object width="445" height="364" class="youTube">' . "\r" .
'	<param name="movie" value="' . $url . '"></param>' . "\r" .
'	<param name="allowFullScreen" value="true"></param>' . "\r" .
'	<param name="allowscriptaccess" value="never"></param>' . "\r" .
'	<embed src="' . $url . '" type="application/x-shockwave-flash"' . "\r" .
'		allowscriptaccess="never" allowfullscreen="true" width="445" height="364"></embed>' . "\r" .
'</object>';
	}
}

?>
