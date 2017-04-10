<?php

/* @var $this CalendarProperty */
/* @var $context String */
/* @var $args mixed */

$line = strToUpper($this->name);

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
	$line .= ";" . strToUpper($name) . "=" . $value;
}

$value = $this->value;
if (!$this->rawValue){
	$value = preg_replace('/[,;]/', '\\\\$0', $value);
}

$line .= ":" . $value;

while(strlen($line) > 74){
	echo substr($line, 0, 74) . "\r\n ";
	$line = substr($line, 74);
}

echo $line . "\r\n";