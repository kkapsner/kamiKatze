<?php

/* @var $this DBItemCollection */
if ($this->count()){
	echo '<ul>';
	foreach ($this as $item){
		/* @var $item DBItem */
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