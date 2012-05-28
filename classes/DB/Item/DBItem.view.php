<?php

/* @var $this DBItem */
$this->view("singleLine", true);

echo "<ul>";

foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
	/* @var $item DBItemFieldOption */
	echo "<li>" .
		$this->html($item->displayName) . ": ";
	if ($item->type === DBItemFieldOption::DB_ITEM){
		$value = $this->{$item->name};
		if (is_array($value)){
			if (count($value)){
				echo "<ul>";
				foreach ($value as $valueItem){
					echo "<li>";
					$valueItem->view("singleLine", true);
					echo "</li>";
				}
				echo "</ul>";
			}
			else {
				echo "---";
			}
		}
		else {
			if ($value !== null){
				$value->view("singleLine", true);
			}
			else {
				echo "---";
			}
		}
	}
	else {
		echo $this->html($this->{$item->name});
	}
	echo "</li>";
}

echo "</ul>";

?>