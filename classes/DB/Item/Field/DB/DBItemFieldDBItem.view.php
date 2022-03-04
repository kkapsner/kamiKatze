<?php
/** @var DBItemFieldDBItem $this */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	$value->view("link|singleLine", true, $args);
}
?>