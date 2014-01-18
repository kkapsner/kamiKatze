<?php

/* @var $this CalendarProperty */
/* @var $context String */
/* @var $args mixed */

echo strToUpper($this->name);

foreach ($this as $name => $value){
	if (
		(
		strpos($value, ",") !== false ||
		strpos($value, ":") !== false ||
		strpos($value, ";") !== false
		)
		&&
		strpos($value, '"') === false
	){
		$value = '"' . $value . '"';
	}
	echo ";" . strToUpper($name) . "=" . $value;
}

$value = $this->value;
if (!$this->rawValue){
	if (
		(
		strpos($value, ",") !== false ||
		strpos($value, ":") !== false ||
		strpos($value, ";") !== false
		)
		&&
		strpos($value, '"') === false
	){
		$value = '"' . $value . '"';
	}
}

echo ":" . $value . "\r\n";
?>
