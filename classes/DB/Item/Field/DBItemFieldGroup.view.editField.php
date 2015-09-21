<table class="group <?php echo $this->html($this->name)?>">
<?php
/* @var $this DBItemFieldArray */
/* @var $context string */
/* @var $args DBItem */
$this->groupFields->view("edit", true, $args);
?> 
</table>