<?php

/* @var $this DBItemPlugable */
$this->viewByName("DBItem", $context, true, $args);

$plugins = DBItemPlugable::getPluginsCLASS(get_class($this));
if (count($plugins)){
	echo "<h2>Plugins</h2><ul>";
	foreach ($plugins as $plugin){
		/* @var $plugin DBItemPlugin */
		echo "<li>";
		$plugin->view(false, true, $this);
		echo "</li>";
	}
	echo "</ul>";
}
?>