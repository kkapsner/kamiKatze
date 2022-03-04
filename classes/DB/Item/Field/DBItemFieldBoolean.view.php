<?php
/** @var DBItemFieldBoolean $this */
/** @var DBItem $args */

echo $this->getValue($args)? $this->trueString: $this->falseString;
?>