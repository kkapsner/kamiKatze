<?php
/* @var $this DBItemFieldDate */
/* @var $context string */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value){
	$value = $value->format("Y-m-d");
}
else {
	$value = "";
}

?><input type="date" format="<?php echo $this->html($this->editFormat);?>" name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($value);?>">