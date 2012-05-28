<?php
/* @var $this DBItemFieldOption */
/* @var $args DBItem */
if ($this->editable){
	?>
<tr>
		<td><?php echo $this->html($this->displayName);?>:</td>
		<td><?php
			$postName = $this->html(get_class($args) . "[" . $args->DBid . "][" . $this->name . "]");
			switch ($this->type){
				case "text":
					?><textarea name="<?php echo $postName;?>"><?php echo $this->html($args->{$this->name});?></textarea><?php
					break;
				case DBItemFieldOption::DB_ITEM:
					switch ($this->correlation){
						case DBItemFieldOption::ONE_TO_ONE: case DBItemFieldOption::N_TO_ONE:
							?>
			<select name="<?php echo $postName;?>">
				<option></option><?php
							$value = $args->{$this->name};
							foreach (DBItem::getByConditionCLASS($this->class) as $hoItem){
								?>
				<option value="<?php
									echo $hoItem->DBid;
									if ($value === $hoItem){ echo '" selected="selected';}
								?>"><?php $hoItem->view("singleLine", true)?></option>
					<?php
							}
							?>
			</select><?php
							break;
						case DBItemFieldOption::ONE_TO_N: case DBItemFieldOption::N_TO_N:
							?>
			<select name="<?php echo $postName;?>[]" multiple="multiple"><?php
							$hmItems = $args->{$this->name};
							foreach (DBItem::getByConditionCLASS($this->class) as $hmItem){
								echo "\n\t\t\t\t" .
									'<option value="' . $hmItem->DBid . '"';
								if (in_array($hmItem, $hmItems, true)){
									echo ' selected="selected"';
								}
								echo '">';
								$hmItem->view("singleLine", true);
								echo "</option>\n";
							}
							?>
			</select><?php
							break;
					}
					break;
				case "enum":
					echo "\n\t\t\t" . '<select name="' . $postName . '">';
					foreach ($this->typeExtension as $value){
						echo "\n\t\t\t\t" .
							'<option value="' . $this->html($value) . '"' .
								($args->{$this->name} === $value? ' selected="selected"': '') . '>' .
								$this->html($value) .
							'</option>';
					}
					echo "\n\t\t\t" . '</select>';
					break;
				default:
					?><input type="text" name="<?php echo $postName;?>" value="<?php echo $this->html($args->{$this->name});?>"><?php
			}
			?></td>
	</tr>
<?php
}
?>