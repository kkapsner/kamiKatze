<?php
/* @var $this DBItem */
?>
<article class="DBItem <?php echo $this->specifier->getClassName();?>">
	<h1><?php $this->view("singleLine", true);?></h1>
	<table>
<?php
foreach (DBItemField::parseClass(get_class($this)) as $item){
	/* @var $item DBItemField */
	if ($item->displayable){
		echo "<tr><td>" . $this->html($item->displayName) . "</td><td>";
		$item->view(false, true, $this);
		echo "</td></tr>";
		$this->emit(new Event("view.field." . $item->name, $this));
	}
}
?>
	</table>
</article>