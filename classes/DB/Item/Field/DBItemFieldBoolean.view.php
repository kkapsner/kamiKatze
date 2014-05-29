<?php
/* @var $this DBItemFieldBoolean */
/* @var $args DBItem */

echo $args->{$this->name}? $this->trueString: $this->falseString;
?>