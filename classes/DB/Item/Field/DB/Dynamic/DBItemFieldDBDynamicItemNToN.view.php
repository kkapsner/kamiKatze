<?php
/** @var DBItemFieldDBDynamicItemOneToN $this */
/** @var DBItem $args */

$value = $this->getValue($args);
if (count($value) === 1){
	$value[0]->view("link|singleLine", true);
}
else {
	echo "<article class=\"" . $this->html($this->name) . "\">";
	foreach ($value as $i => $collection){
		if (count($collection)){
			echo "<h4>" . $this->html($this->class[$i]) . "</h4>";
			$collection->view("link|singleLine", true);
		}
	}
	echo "<article>";
}