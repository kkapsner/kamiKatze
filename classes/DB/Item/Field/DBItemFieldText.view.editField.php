<?php
/* @var $this DBItemFieldText */
/* @var $context string */
/* @var $args DBItem */

?><textarea name="<?php echo $this->getPostName($args);?>"><?php echo $this->html($args->{$this->name});?></textarea>