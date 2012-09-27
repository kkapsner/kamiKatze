<?php
/* @var $this DBItemFieldOption */
/* @var $args DBItem */

$value = $args->{$this->name};
if (is_a($value, "Viewable")){
	$value->view("singleLine", true, $args);
}
elseif ($value === null){
	echo "---";
}
else {
	echo $this->html($value);
}

if ($this->extender && $value !== null){
	echo "<ul>";
	foreach ($this->extensionFieldOptions[$value] as $subItem){
		/* @var $subItem DBItemFieldOption */
		echo "<li>" . $this->html($subItem->displayName) . ": ";
		$subItem->view(false, true, $args);
		echo "</li>";
	}
	echo "</ul>";
}
?>