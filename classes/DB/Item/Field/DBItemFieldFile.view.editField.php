<?php
/* @var $this DBItemFieldFile */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
$value = $args->{$this->name};
if ($value !== null){
	echo '<input type="hidden" name="' . $postName . '[fileID]" value="' . $this->html($value->DBid) . '">' .
		'<input name="' . $postName . '[filename]" value="'  . $this->html($value->filename) . '" title="clear to delete file">' .
		'<br>';
}
?><input type="file" name="<?php echo $postName;?>"><input type="hidden" name="<?php echo $postName;?>[path]" value="<?php echo $this->html(json_encode($this->getPostName($args, true)));?>">