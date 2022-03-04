<?php
/** @var DBItemField $this */
/** @var DBItem $args */

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