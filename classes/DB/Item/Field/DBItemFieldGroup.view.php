<table class="group <?php echo $this->html($this->name)?>">
<?php
/** @var DBItemFieldArray $this */
/** @var DBItem $args */

$this->groupFields->view("rows", true, $args);
?>
</table>