<?php
/* @var $this DBItemFieldEnum */
/* @var $context string */
/* @var $args DBItem */
$postName = $this->getPostName($args);

echo "\n\t\t\t" . '<input type="hidden" name="' . $postName . '" value="0">' .
	 "\n\t\t\t" . '<input type="checkbox" name="' . $postName . '" value="1"' . ($this->getValue($args)? ' checked="checked"': '') . '>';

?>