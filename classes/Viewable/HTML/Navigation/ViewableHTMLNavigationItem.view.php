<li<?php
	/** @var ViewableHTMLNavigationItem $this */
	$className = $this->getHTMLAttribute("class");
	if($this->active) $className .= " active";
	if($className) echo ' class="' . $this->html($className) . '"';
?>>
	<a href=<?php
		echo '"' . $this->url($this->url) . '"';
		if($this->extern) echo ' target="_blank"';
		
	?>>
		<?php
			if ($this->innerHTML) echo $this->innerHTML;
			else echo $this->html($this->text);
		?>
	</a>
	<?php
		if ($this->subNavigation) $this->subNavigation->view($context, true);
	?>
</li>