<?php
/** @var DBItemFieldText $this */
/** @var string $context */
/** @var DBItem $args */

?><textarea name="<?php echo $this->getPostName($args);?>"><?php echo $this->html($this->getValue($args));?></textarea>