<?php
/** @var DBItemFieldOption $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
$eventBase = "view.editField." . $this->name;

$args->emit($eventBase . ".beforeHidden");
echo "\n\t\t\t" . '<input type="hidden" name="' . $postName . '[present]" value="1" >';
$args->emit($eventBase . ".afterHidden");

$values = $this->getValue($args);
foreach ($this->typeExtension as $value){
	$args->emit($eventBase . ".checkbox.start." . $value);
	
	echo "\n\t\t\t";
	$beforeCheckboxEvent = $args->emit($eventBase . ".checkbox.beforeCheckbox." . $value);
	if (!$beforeCheckboxEvent->getDefaultPrevented()){
		echo "<label>";
	}
	
	echo '<input type="checkbox" name="' . $postName . '[values][]" value="' . $this->html($value) . '"' .
			(($values !== null && in_array($value, $values))? ' checked="checked"': '') . '>';
	
	$args->emit($eventBase . ".checkbox.afterCheckbox." . $value);
	
	if (!$args->emit($eventBase . ".checkbox.text." . $value)->getDefaultPrevented()){
		echo $this->html($value);
	}
	
	if (!$beforeCheckboxEvent->getDefaultPrevented()){
		echo "</label><br>";
	}
	$args->emit($eventBase . ".checkbox.end." . $value);
}
?>