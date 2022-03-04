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

if ($value !== null){
	echo "<ul>";
	foreach ($this->extensionFieldOptions[$value] as $subItem){
		/** @var DBItemField $subItem */
		if ($subItem->displayable){
			echo "<li>" . $this->html($subItem->displayName) . ": ";
			$subItem->view(false, true, $args);
			echo "</li>";
		}
	}
	echo "</ul>";
}
?>