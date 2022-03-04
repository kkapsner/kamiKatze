<?php

/** @var CalendarObject $this */
/** @var String $context */
/** @var mixed $args */
$name = $this->getName();

echo "BEGIN:" . $name . "\r\n";

foreach ($this->properties as $property){
	$property->view($context, true, $args);
}

foreach ($this as $obj){
	$obj->view($context, true, $args);
}

echo "END:" . $name . "\r\n";