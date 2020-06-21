<?php

$first = true;
foreach ($this as $item){
	/* @var $item DBItem */
	if (!$first){
		echo array_read_key("delimiter", $args, ", ");
	}
	$first = false;
	$item->view("singleLine", true, $args);
}