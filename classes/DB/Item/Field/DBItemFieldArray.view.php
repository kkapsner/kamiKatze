<?php
/** @var DBItemFieldArray $this */
/** @var DBItem $args */

$values = $this->getValue($args);

if (count($values)){
	echo "<table>\n";
	$this->arrayFields->view("table.header", true);
	foreach ($values as $value){
		$this->arrayFields->view("table.row", true, (object) $value);
	}
	echo "</table>\n";
}
else {
	echo "<i>empty</i>";
}
?>