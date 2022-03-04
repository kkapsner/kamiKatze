<?php
/** @var DBItemFieldCollection $this */
/** @var string $context */
/** @var DBItem $args */

	foreach ($this as $item){
		/** @var DBItemField $item */
		$item->view($context, true, $args);
		$this->emit(new Event("view.edit.field." . $item->name, $args));
	}
?>