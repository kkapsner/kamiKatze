<table class="group <?php echo $this->html($this->name)?>">
<?php
/** @var DBItemFieldArray $this */
/** @var string $context */
/** @var DBItem $args */
$this->groupFields->view("edit", true, $args);
?> 
</table>