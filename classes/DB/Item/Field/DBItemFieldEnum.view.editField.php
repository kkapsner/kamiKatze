<?php
/** @var DBItemFieldEnum $this */
/** @var string $context */
/** @var DBItem $args */
$postName = $this->getPostName($args);

echo "\n\t\t\t" . '<select name="' . $postName . '">';
if ($this->null){
	echo "\n\t\t\t\t" .
		'<option value=""' .
			($this->getValue($args) === null? ' selected="selected"': '') .
		'>' .
			'---' .
		'</option>';
}
foreach ($this->typeExtension as $value){
	echo "\n\t\t\t\t" .
		'<option value="' . $this->html($value) . '"' .
			($this->getValue($args) === $value? ' selected="selected"': '') . '>' .
			$this->html($value) .
		'</option>';
}
echo "\n\t\t\t" . '</select>';
?>