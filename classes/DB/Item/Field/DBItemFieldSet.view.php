<?php
/* @var $this DBItemFieldSet */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	echo "<ul>";
	foreach ($value as $v){
		echo "<li>" . $this->html($v) . "</li>";
	}
	echo "</ul>";
}
?>