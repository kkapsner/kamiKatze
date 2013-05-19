<?php

/* @var $this CalendarObject */
/* @var $context String */
/* @var $args mixed */
$name = $this->getName();

echo "BEGIN:" . $name . "\r\n";

foreach ($this->getRequiredProperties() as $prop){
	$value = $this->$prop;
	$value->view($context, true, $args);
}
foreach ($this->getOptionalProperties() as $prop){
	$value = $this->$prop;
	if ($value){
		$value->view($context, true, $args);
	}
}

foreach ($this as $obj){
	$obj->view($context, true, $args);
}

echo "END:" . $name . "\r\n";
?>
