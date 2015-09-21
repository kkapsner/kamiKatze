<?php
/* @var $this DBItemFieldCollection */
/* @var $context string */
/* @var $args DBItem */

	foreach ($this as $item){
		/* @var $item DBItemField */
		$item->view("row", true, $args);
		$this->emit(new Event("view.field." . $item->name, $args));
	}
?>