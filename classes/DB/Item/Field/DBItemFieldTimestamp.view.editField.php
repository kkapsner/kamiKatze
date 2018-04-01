<?php
/* @var $this DBItemFieldText */
/* @var $context string */
/* @var $args DBItem */

?><input name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($this->getValue($args)->format(is_array($this->editFormat)? $this->editFormat[0]: $this->editFormat));?>">