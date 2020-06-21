<?php
/* @var $this DBItemFieldTimestamp */
/* @var $context string */
/* @var $args DBItem */

$value = $this->getValue($args);
if (!$value){
	$value = new DateTime();
}

?><input name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($value->format(is_array($this->editFormat)? $this->editFormat[0]: $this->editFormat));?>">