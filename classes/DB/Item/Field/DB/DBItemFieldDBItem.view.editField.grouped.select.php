<?php
/* @var $this DBItemFieldDBItem */
/* @var $context string */
/* @var $args array(
 *		"postName" => post name,
 *		"groups" => available items in groups,
 *		"value" =>  current item) */

$groups = array();
$removedGroup = false;
foreach ($args["groups"] as $groupName => $group){
	if (count($group) > 0){
		$groups[$groupName] = $group;
	}
	else {
		$removedGroup = true;
	}
}

if (!$removedGroup && count($groups) === 1){
	$args["availableItems"] = array_values($groups)[0];
	$this->viewByName(
		"DBItemFieldDBItem",
		"editField.select",
		true,
		$args
	);
}
else {
?>
			<select name="<?php echo $args["postName"];?>">
				<option></option><?php
		$value = $args["value"];
		foreach ($groups as $groupName => $group){
			if ($groupName){
				echo "<optgroup label=\"" . $this->html($groupName) . "\">";

			}

			foreach ($group as $hoItem){
				?>
				<option value="<?php
				echo get_class($hoItem) . "#" . $hoItem->DBid;
				if ($value === $hoItem){ echo '" selected="selected';}
			?>"><?php $hoItem->view("singleLine", true)?></option>
					<?php
			}
			if ($groupName){
				echo "</optgroup>";
			}
		}
		?>
			</select><?php
}?>