<?php
/* @var $this DBItem */
?>
<article class="DBItem <?php echo $this->specifier->getClassName();?>">
	<h1><?php $this->view("singleLine", true);?></h1>
	<ul>
<?php
foreach (DBItemField::parseClass(get_class($this)) as $item){
	/* @var $item DBItemField */
	if ($item->displayable){
		echo "<li>" . $this->html($item->displayName) . ": ";
		$item->view(false, true, $this);
		echo "</li>";
	}
}
?>
	</ul>
</article>