<input type="hidden" name="id" value="<?php echo $this->html($this->DBid);?>">
<input type="hidden" name="class" value="<?php echo $this->html(get_class($this));?>">
<table class="dbItem">
	<?php
	/* @var $this DBItem */
	$this->emit(new Event("view.edit.fields.start", $this));
	foreach (DBItemField::parseClass(get_class($this)) as $item){
		/* @var $item DBItemField */
		$item->view($context, true, $this);
		$this->emit(new Event("view.edit.field." . $item->name, $this));
	}
	$this->emit(new Event("view.edit.fields.end", $this));
	?>
</table>