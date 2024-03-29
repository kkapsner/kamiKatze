<?php
/** @var DBItem $this */
?>
<article class="DBItem <?php echo $this->specifier->getClassName();?>">
<?php $this->view("header", true);?>
	<table>
<?php
$this->emit(new Event("view.fields.start", $this));
foreach (DBItemField::parseClass(get_class($this)) as $item){
	/** @var DBItemField $item */
	if ($item->displayable){
		echo "<tr><td>" . $this->html($item->displayName) . "</td><td>";
		$item->view(false, true, $this);
		echo "</td></tr>";
		$this->emit(new Event("view.field." . $item->name, $this));
	}
}
$this->emit(new Event("view.fields.end", $this));
?>
	</table>
</article>