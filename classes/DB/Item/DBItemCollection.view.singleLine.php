<?php

/** @var DBItemCollection $this */
if ($this->count()){
	echo '<ul>';
	foreach ($this as $item){
		/** @var DBItem $item */
		echo '<li>';
		$item->view("link|singleLine", true, $args);
		echo '</li>';
	}
	echo '</ul>';
}
else {
	echo '<em>empty</em>';
}

?>