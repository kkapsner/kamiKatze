<?php
/* @var $this DBItemField */
/* @var $args DBItem */

$value = $this->getValue($args);
if ($value === null){
	echo "---";
}
else {
	echo "<span>" . $this->html($value) . "</span>";
	?>
<script type="text/javascript">
	(function(){
		var span = document.getElementsByTagName("span");
		span = span[span.length - 1];
		var clientRects = span.getClientRects();
		if (clientRects.length > 1){
			var height = (clientRects[0].bottom - clientRects[0].top) + "px";
			var div = document.createElement("div");
			div.style.height = height;
			div.style.overflow = "hidden";
			div.style.position = "relative";
			span.parentNode.replaceChild(div, span);
			div.appendChild(span);
			
			var button = document.createElement("div");
			button.style.cssText = "position: absolute; top: 4px; left: 4px; " +
				"border: 1px solid; line-height: 0.7em; height: 0.7em; width: 0.7em; cursor: pointer; text-align: center;";
			button.innerHTML = "+";
			div.appendChild(button);
			div.style.paddingLeft = (button.offsetWidth + 8) + "px";
			button.onclick = function(){
				if (div.style.height){
					div.style.height = "";
					this.innerHTML = "&minus;";
				}
				else {
					div.style.height = height;
					this.innerHTML = "+";
				}
			}
		}
	})();
</script><?php
}
?>