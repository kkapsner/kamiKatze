<section class="DBItemPlugable"><?php

/** @var DBItemPlugable $this */
$this->viewByName("DBItem", $context, true, $args);

$plugins = DBItemPlugable::getPluginsCLASS(get_class($this));
if (count($plugins)){
	echo "<article class=\"Plugins\"><h2>Plugins</h2><ul>";
	foreach ($plugins as $plugin){
		/** @var DBItemPlugin $plugin */
		echo "<li>";
		$plugin->view(false, true, $this);
		echo "</li>";
	}
	echo "</ul></article>";
}
?>
</section>