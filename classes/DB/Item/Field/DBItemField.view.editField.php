<?php
/** @var DBItemField $this */
/** @var string $context */
/** @var DBItem $args */

?><input type="text" name="<?php echo $this->getPostName($args);?>" value="<?php echo $this->html($this->getValue($args));?>">