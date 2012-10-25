<?php
/* @var $this DBItemFieldEnum */
/* @var $context string */
/* @var $args DBItem */
$postName = $this->getPostName($args);

echo "\n\t\t\t" . '<select name="' . $postName . '">';
if ($this->null){
	echo "\n\t\t\t\t" .
		'<option value=""' .
			($args->{$this->name} === null? ' selected="selected"': '') .
		'>' .
			'---' .
		'</option>';
}
foreach ($this->typeExtension as $value){
	echo "\n\t\t\t\t" .
		'<option value="' . $this->html($value) . '"' .
			($args->{$this->name} === $value? ' selected="selected"': '') . '>' .
			$this->html($value) .
		'</option>';
}
echo "\n\t\t\t" . '</select>';
?>