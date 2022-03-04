<?php
/** @var DBItemFieldOption $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
echo "\n\t\t\t" . '<input type="hidden" name="' . $postName . '[present]" value="1" >';
echo "\n\t\t\t" . '<select name="' . $postName . '[values][]" multiple="multiple" >';
$values = $this->getValue($args);
foreach ($this->typeExtension as $value){
	echo "\n\t\t\t\t" .
		'<option value="' . $this->html($value) . '"' .
			(($values !== null && in_array($value, $values))? ' selected="selected"': '') . '>' .
			$this->html($value) .
		'</option>';
}
echo "\n\t\t\t" . '</select>';
?>