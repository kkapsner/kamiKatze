<?php
/* @var $this DBItemFieldCollection */
/* @var $context string */
/* @var $args DBItem */

	foreach ($this as $item){
		/* @var $item DBItemField */
		$item->view($context, true, $args);
		$this->emit(new Event("view.edit.field." . $item->name, $args));
	}
?>