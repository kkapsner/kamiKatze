<?php

/** @var DBItemFieldFileItem $this */
/** @var DBItemFieldFile $args */

?><a href="<?php echo $this->url($this->URL);?>" target="_blank"><?php
if (($args instanceof DBItemFieldFile) && $args->image){
	echo '<img class="DBItemFieldFileItem ' . $this->html($args->name) . '"' .
		' alt="' . $this->html($this->filename) . '"' .
		' src="' . $this->url($this->URL) . '">';
}
else {
	echo $this->html(basename($this->path));
}
?></a>
