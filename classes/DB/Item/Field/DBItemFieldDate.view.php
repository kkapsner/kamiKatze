<?php
/** @var DBItemFieldDate $this */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	echo $this->html($value->format($this->displayFormat));
}