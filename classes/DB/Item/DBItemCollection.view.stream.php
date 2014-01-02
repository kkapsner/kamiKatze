<?php

/* @var $this DBItemCollection */

foreach ($this as $item){
	/* @var $item DBItem */
	$item->view($context, true, $args);
}

?>