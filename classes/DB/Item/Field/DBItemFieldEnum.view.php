<?php
/* @var $this DBItemFieldEnum */
/* @var $args DBItem */

$value = $args->{$this->name};
if ($value === null){
	echo "---";
}
else {
	echo $this->html($value);
}
?>