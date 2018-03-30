<?php
/* @var $this DBItemFieldText */
/* @var $context string */
/* @var $args DBItem */

?><input name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($this->getValue($args)->format($this->editFormat));?>">