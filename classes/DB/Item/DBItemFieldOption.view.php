<?php
/* @var $this DBItemFieldOption */
/* @var $args DBItem */

$value = $args->{$this->name};
if (is_a($value, "Viewable")){
	$value->view("singleLine", true, $args);
}
elseif ($value === null){
	echo "---";
}
else {
	echo $this->html($value);
}
?>