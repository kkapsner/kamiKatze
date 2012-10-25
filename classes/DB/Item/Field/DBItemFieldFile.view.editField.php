<?php
/* @var $this DBItemFieldFile */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
$value = $args->{$this->name};
if ($value !== null){
	echo "File present (" . $this->html(basename($value->path)) . ")";
}
?><input type="file" name="<?php echo $postName;?>"><input type="hidden" name="<?php echo $postName;?>" value="<?php echo $this->html(json_encode($this->getPostName($args, true)));?>">