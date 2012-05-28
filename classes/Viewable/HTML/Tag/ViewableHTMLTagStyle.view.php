<?php

/* @var $this ViewableHTMLTagStyle */
if ($this->ie){
	echo "<!--[if IE]>";
}

if ($this->style){
	echo "<" . $this->tagName;
	foreach ($this->getHTMLAttirbuteList() as $name){
		echo $this->getHTMLAttribute($name, true);
	}
	echo ">";

	if ($this->code){
		echo str_replace("</style", '<\/style', $this->style);
	}

	echo "</" . $this->tagName . ">";
}
else {
	echo '<link rel="stylesheet"';
	foreach ($this->getHTMLAttirbuteList() as $name){
		if ($name === "src"){
			echo ' href="' . $this->url($this->getHTMLAttribute("src", false)->value) . '"';
		}
		else {
			echo $this->getHTMLAttribute($name, true);
		}
	}
	echo '>';
}

if ($this->ie){
	echo "<![endif]-->";
}
?>