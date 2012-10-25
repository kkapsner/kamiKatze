<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);

if (
	!$this->canOverwriteOthers &&
	(
		$this->correlation === DBItemFieldDBItem::ONE_TO_ONE ||
		$this->correlation === DBItemFieldDBItem::ONE_TO_N
	)
){
	$db = DB::getInstance();
	$fieldName = $db->quote($this->correlationName, DB::PARAM_IDENT);
	$availableItems = DBItem::getByConditionCLASS($this->class, $fieldName . " IS NULL OR " . $fieldName . " = " . $args->DBid);
}
else {
	$availableItems = DBItem::getByConditionCLASS($this->class);
}

switch ($this->correlation){
	case DBItemFieldDBItem::ONE_TO_ONE: case DBItemFieldDBItem::N_TO_ONE:
							?>
			<select name="<?php echo $postName;?>">
				<option></option><?php
		$value = $args->{$this->name};
		foreach ($availableItems as $hoItem){
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
	case DBItemFieldDBItem::ONE_TO_N: case DBItemFieldDBItem::N_TO_N:
		?>
			<input type="hidden" name="<?php echo $postName;?>[present]" value="1">
			<select name="<?php echo $postName;?>[values][]" multiple="multiple"><?php
		$hmItems = $args->{$this->name};
		foreach ($availableItems as $hmItem){
			echo "\n\t\t\t\t" .
				'<option value="' . $hmItem->DBid . '"';
			if ($hmItems->contains($hmItem, true)){
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
?>