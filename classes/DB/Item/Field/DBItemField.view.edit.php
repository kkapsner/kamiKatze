<?php
/** @var DBItemField $this */
/** @var string $context */
/** @var DBItem $args */
if ($this->editable){
	?>
	<tr class="<?php echo $this->html($this->name);?>">
		<td><?php echo $this->html($this->displayName);?>:</td>
		<td>
			<?php $this->view("editField", true, $args);?>
		</td>
	</tr>
<?php
}
?>