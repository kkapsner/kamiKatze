<?php
/* @var $this DBItemFieldOption */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
echo "\n\t\t\t" . '<input type="hidden" name="' . $postName . '[present]" value="1" >';
$values = $args->{$this->name};
foreach ($this->typeExtension as $value){
	echo "\n\t\t\t" .
		'<label><input type="checkbox" name="' . $postName . '[values][]" value="' . $this->html($value) . '"' .
			(($values !== null && in_array($value, $values))? ' checked="checked"': '') . '>' .
			$this->html($value) . '</label>';
	$args->emit(new Event("view.editField.checkbox." . $value . ".end", $args));
	echo '<br>';
}
?>