<?php
/** @var DBItemFieldEnum $this */
/** @var string $context */
/** @var DBItem $args */
$postName = $this->getPostName($args);

if ($args->DBid === 0){
	$this->viewByName("DBItemFieldEnum", $context, true, $args);
	echo '<script>(function(){
		var sel = document.getElementsByTagName("select");
		sel = sel[sel.length - 1];
		var oldWindowOnload = window.onload;
		window.onload = function(ev){
			if (oldWindowOnload){
				oldWindowOnload.apply(this, arguments);
			}
			var tables = {};
			var currentTable = null;
			Array.prototype.slice.call(sel.parentNode.getElementsByTagName("table")).forEach(function(table){
				if (table.parentNode === sel.parentNode){
					var extension = table.getAttribute("data-extension");
					tables[extension] = table;
					if (extension !== sel.value){
						table.parentNode.removeChild(table);
					}
					else {
						currentTable = table;
					}
				}
			});
			
			sel.onchange = function(){
				currentTable.parentNode.replaceChild(tables[sel.value], currentTable);
				currentTable = tables[sel.value];
			};
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
	$value = $this->getValue($args);

	echo "\n\t\t\t" .'<table class="dbItemExtender" data-extension="' . $this->html($value) . '">' .
		'<caption>' . $this->html($value) . "</caption>";
	foreach ($this->extensionFieldOptions[$value] as $subItem){
		$subItem->view("edit", true, $args);
	}
	echo "\n\t\t\t" . '</table>';
}
?>