<?php
/** @var DBItemFieldCollection $this */
/** @var string $context */
/** @var DBItem $args */

	foreach ($this as $item){
		/** @var DBItemField $item */
		$item->view("row", true, $args);
		$this->emit(new Event("view.field." . $item->name, $args));
	}
?>