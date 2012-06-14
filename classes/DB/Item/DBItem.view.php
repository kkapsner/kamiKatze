<?php

/* @var $this DBItem */
$this->view("singleLine", true);

echo "<ul>";

foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
	/* @var $item DBItemFieldOption */
	echo "<li>" . $this->html($item->displayName) . ": ";
	$value = $this->{$item->name};
	if (is_a($value, "Viewable")){
		$value->view("singleLine", true, $args);
	}
	elseif ($value === null){
		echo "---";
	}
	else {
		echo $this->html($value);
	}
	echo "</li>";
}

echo "</ul>";

?>