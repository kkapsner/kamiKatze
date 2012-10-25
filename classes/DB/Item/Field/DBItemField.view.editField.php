<?php
/* @var $this DBItemField */
/* @var $context string */
/* @var $args DBItem */

?><input type="text" name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($args->{$this->name});?>">