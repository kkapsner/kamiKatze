<?php
/* @var $this DBItemFieldSet */
/* @var $args DBItem */

$value = $args->{$this->name};
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