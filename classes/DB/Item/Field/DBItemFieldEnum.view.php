<?php
/* @var $this DBItemFieldEnum */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	echo $this->html($value);
}
?>