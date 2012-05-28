<?php

/**
 * @author kkapsner
 */
class BBCodeTagYoutube extends BBCodeTag{
	protected static $type = "inline";
	protected static $allowedChildren = array("text");
	protected static $allowedParents = array("block", "inline");
	

	/**
	 * The parameter list.
	 * @var array
	 */
	protected $parameter = array("border" => 0);
	

	/**
	 * Generates HTML.
	 * @return string
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
