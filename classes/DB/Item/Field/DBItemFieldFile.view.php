<?php
/** @var DBItemField $this */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	$value->view($context, true, $this);
}
?>