<?php

/* @var $this DBItem */
$this->view("singleLine", true);

echo "<ul>";

foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
	/* @var $item DBItemFieldOption */
	echo "<li>" . $this->html($item->displayName) . ": ";
	$item->view(false, true, $this);
	echo "</li>";
}

echo "</ul>";

$extensions = DBItem::getExtensionsCLASS(get_class($this));
if (count($extensions)){
	echo "<h2>Extensions</h2><ul>";
	foreach ($extensions as $extension){
		/* @var $extension DBItemExtension */
		echo "<li>";
		$extension->view(false, true, $this);
		echo "</li>";
	}
	echo "</ul>";
}
?>