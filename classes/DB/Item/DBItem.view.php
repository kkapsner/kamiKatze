<?php

/* @var $this DBItem */
$this->view("singleLine", true);

echo "<ul>";

foreach (DBItemField::parseClass(get_class($this)) as $item){
	/* @var $item DBItemField */
	if ($item->displayable){
		echo "<li>" . $this->html($item->displayName) . ": ";
		$item->view(false, true, $this);
		echo "</li>";
	}
}

echo "</ul>";
?>