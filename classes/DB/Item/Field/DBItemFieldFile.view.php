<?php
/* @var $this DBItemField */
/* @var $args DBItem */

$value = $args->{$this->name};
if ($value === null){
	echo "---";
}
else {
	$value->view($context, true);
}
?>