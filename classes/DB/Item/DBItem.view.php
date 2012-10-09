<?php

/* @var $this DBItem */
$this->view("singleLine", true);

echo "<ul>";

foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
	/* @var $item DBItemFieldOption */
	if ($item->displayable){
		echo "<li>" . $this->html($item->displayName) . ": ";
		$item->view(false, true, $this);
		echo "</li>";
	}
}

echo "</ul>";
?>