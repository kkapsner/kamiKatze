<?php
/** @var DBItemFieldTimestamp $this */
/** @var string $context */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value){
	$value = $value->format(is_array($this->editFormat)? $this->editFormat[0]: $this->editFormat);
}
else {
	$value = "";
}

?><input name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($value);?>">