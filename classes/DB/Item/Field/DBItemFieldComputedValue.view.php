<?php
/* @var $this DBItemField */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
elseif ($value instanceof Viewable) {
	$value->view("link|singleLine", true);
}
else {
	echo $this->html($value);
}
?>