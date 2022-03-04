<?php
/** @var DBItemFieldDBItem $this */
/** @var string $context */
/** @var array $args(
 *		"postName" => post name,
 *		"availableItems" => available items,
 *		"value" =>  current items) */
?>
			<input type="hidden" name="<?php echo $args["postName"];?>[present]" value="1">
			<select name="<?php echo $args["postName"];?>[values][]" multiple="multiple">
				<?php
		$hmItems = $args["value"];
		foreach ($args["availableItems"] as $hmItem){
			echo "\n\t\t\t\t" .
				'<option value="' . $hmItem->DBid . '"';
			if ($hmItems->contains($hmItem, true)){
				echo ' selected="selected"';
			}
			echo '>';
			$hmItem->view("singleLine", true);
			echo "</option>\n";
		}
		?>
			</select>