<?php
/** @var DBItemFieldFile $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
$value = $this->getValue($args);
if ($value !== null){
	echo '<input type="hidden" name="' . $postName . '[fileID]" value="' . $this->html($value->DBid) . '">' .
		'<input name="' . $postName . '[filename]" value="'  . $this->html($value->filename) . '" title="clear to delete file">' .
		'<br>';
}
?><input type="file" name="<?php echo $postName;?>"><input type="hidden" name="<?php echo $postName;?>[path]" value="<?php echo $this->html(json_encode($this->getPostName($args, true)));?>">