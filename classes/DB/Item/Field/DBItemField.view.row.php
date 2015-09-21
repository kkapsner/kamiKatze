<?php
/* @var $this DBItemField */
/* @var $context string */
/* @var $args DBItem */
if ($this->displayable){
	?>
	<tr class="<?php echo $this->html($this->name);?>">
		<td><?php echo $this->html($this->displayName);?>:</td>
		<td>
			<?php $this->view(false, true, $args);?>
		</td>
	</tr>
<?php
}
?>