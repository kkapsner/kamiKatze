<?php
/** @var DBItemFieldDate $this */
/** @var string $context */
/** @var DBItem $args */

$value = $this->getValue($args);
if ($value){
	$value = $value->format("Y-m-d");
}
else {
	$value = "";
}

$required = $this->null? "": " required=\"required\"";

?><input type="date" format="<?php echo $this->html($this->editFormat);?>" name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($value);?>"<?php echo $required;?>>