<?php

/* @var $this CalendarObject */
/* @var $context String */
/* @var $args mixed */
$name = $this->getName();

echo "BEGIN:" . $name . "\r\n";

foreach ($this->properties as $property){
	$property->view($context, true, $args);
}

foreach ($this as $obj){
	$obj->view($context, true, $args);
}

echo "END:" . $name . "\r\n";