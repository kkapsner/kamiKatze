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

if ($value !== null){
	echo "<ul>";
	foreach ($this->extensionFieldOptions[$value] as $subItem){
		/* @var $subItem DBItemField */
		if ($subItem->displayable){
			echo "<li>" . $this->html($subItem->displayName) . ": ";
			$subItem->view(false, true, $args);
			echo "</li>";
		}
	}
	echo "</ul>";
}
?>