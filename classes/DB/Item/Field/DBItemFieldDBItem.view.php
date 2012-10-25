<?php
/* @var $this DBItemFieldDBItem */
/* @var $args DBItem */

$value = $args->{$this->name};
if ($value === null){
	echo "---";
}
else {
	$value->view("singleLine", true, $args);
}
?>