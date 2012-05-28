<input type="hidden" name="id" value="<?php echo $this->html($this->DBid);?>">
<input type="hidden" name="class" value="<?php echo $this->html(get_class($this));?>">
<table class="dbItem">
	<?php
	/* @var $this DBItem */
	foreach (DBItemFieldOption::parseClass(get_class($this)) as $item){
		/* @var $item DBItemFieldOption */
		$item->view($context, true, $this);
	}
	?>
</table>