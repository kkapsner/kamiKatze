<?php

$first = true;
foreach ($this as $item){
	/* @var $item DBItem */
	if (!$first){
		echo ", ";
	}
	$first = false;
	$item->view("singleLine", true, $args);
}