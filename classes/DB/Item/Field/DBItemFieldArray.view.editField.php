<?php
/* @var $this DBItemFieldArray */
/* @var $context string */
/* @var $args DBItem */

$postName = $this->getPostName($args);
$values = $this->getValue($args);
$this->currentItem = $args;
echo "<table>\n";
$this->arrayFields->view("table.header", true, array("delete"));
if (count($values)){
	foreach ($values as $value){
		$this->view("editRow", true, array($postName, $value));
	}
}
	?><tr>
		<td>
			<button type="button">create new</button>
			<script type="text/data"><?php
				$this->view("editRow", true, array($postName, DBItem::getCLASS($this->linkSpecifier, 0)));
			?></script>
			<script type="text/javascript">
				(function(){
					var sc = document.getElementsByTagName("script");
					sc = sc[sc.length - 2];
					var button = document.getElementsByTagName("button");
					button = button[button.length - 1];
					var newID = 0;
					button.onclick = function(){
						var div = document.createElement("div");
						var html = sc.text.replace(/(?:\<|&lt;)new(?:\>|&gt;)/g, newID);
						newID++;
						div.innerHTML = "<table>" + html + "</table>";
						button.parentNode.parentNode.parentNode.insertBefore(
							div.getElementsByTagName("tr")[0],
							button.parentNode.parentNode
						);
					}
				})();
			</script>
		</td>
	</tr><?php
echo "</table>\n";

$this->currentItem = null;
?> 