<?php
/** @var DBItemFieldExternalItem $this */
/** @var string $context */
/** @var DBItem $args */

$postName = $this->getPostName($args);
$availableItems = call_user_func(array($this->class, "getAll"));
?>
			<select name="<?php echo $postName;?>">
				<?php
				if ($this->null){
					echo "<option></option>";
				}
$value = $this->getValue($args);
foreach ($availableItems as $item){
	?>
				<option value="<?php
	echo $item->getId();
	if ($value === $item){ echo '" selected="selected';}
?>"><?php $item->view("singleLine", true)?></option>
<?php
}
?>
			</select>