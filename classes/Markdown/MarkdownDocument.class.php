<?php

/**
 * MarkdownDocument definition file
 */

/**
 * Container for the markdown lines
 *
 * @author kkapsner
 */
class MarkdownDocument extends Collection{
	public function __construct(){
		parent::__construct("MarkdownElement");
	}
}

?>
