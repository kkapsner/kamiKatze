<?php
/* @var $this DBItemFieldEnum */
/* @var $context string */
/* @var $args DBItem */
$postName = $this->getPostName($args);

if ($args->DBid === 0){
	$this->viewByName("DBItemFieldEnum", $context, true, $args);
	echo '<script>(function(){
		var sel = document.getElementsByTagName("select");
		sel = sel[sel.length - 1];
		var tables = sel.parentNode.getElementsByTagName("table");
		sel.onchange = function(){
			for (var i = 0; i < tables.length; i++){
				tables[i].style.display = (tables[i].getAttribute("data-extension") === sel.value)? "": "none";
			}
		}
		var oldWindowOnload = window.onload;
		window.onload = function(ev){
			if (oldWindowOnload){
				oldWindowOnload.call(window, ev);
			}
			sel.onchange();
		}
	})();</script>';
	foreach ($this->typeExtension as $value){
		echo "\n\t\t\t" .'<table class="dbItemExtender" data-extension="' . $this->html($value) . '">' .
			'<caption>' . $this->html($value) . "</caption>";
		foreach ($this->extensionFieldOptions[$value] as $subItem){
			$subItem->view("edit", true, $args);
		}
		echo "\n\t\t\t" . '</table>';
	}
}
else {
	$value = $args->{$this->name};

	echo "\n\t\t\t" .'<table class="dbItemExtender" data-extension="' . $this->html($value) . '">' .
		'<caption>' . $this->html($value) . "</caption>";
	foreach ($this->extensionFieldOptions[$value] as $subItem){
		$subItem->view("edit", true, $args);
	}
	echo "\n\t\t\t" . '</table>';
}
?>