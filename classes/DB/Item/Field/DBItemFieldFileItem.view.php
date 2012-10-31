<?php

/* @var $this DBItemFieldFileItem */
/* @var $args DBItemFieldFile */

?><a href="<?php echo $this->url($this->URL);?>" target="_blank"><?php
if (($args instanceof DBItemFieldFile) && $args->image){
	echo '<img class="DBItemFieldFileItem ' . $this->html($args->name) . '"' .
		' alt="' . $this->html(basename($this->path)) . '"' .
		' src="' . $this->url($this->URL) . '">';
}
else {
	echo $this->html(basename($this->path));
}
?></a>
