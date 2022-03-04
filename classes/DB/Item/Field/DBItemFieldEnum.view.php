<?php
/** @var DBItemFieldEnum $this */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	echo $this->html($value);
}
?>