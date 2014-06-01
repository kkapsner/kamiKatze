<?php
/* @var $this DBItemField */
/* @var $args DBItem */

$value = $args->{$this->name};
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