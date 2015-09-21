<table class="group <?php echo $this->html($this->name)?>">
<?php
/* @var $this DBItemFieldArray */
/* @var $args DBItem */

$this->groupFields->view("rows", true, $args);
?>
</table>