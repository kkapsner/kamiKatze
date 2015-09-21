<?php
/* @var $this DBItemFieldBoolean */
/* @var $args DBItem */

echo $this->getValue($args)? $this->trueString: $this->falseString;
?>