<?php
/* @var $this DBItemFieldDBItem */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	$value->view("link|singleLine", true, $args);
}
?>