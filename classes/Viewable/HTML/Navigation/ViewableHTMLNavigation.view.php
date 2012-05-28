<nav<?php
/* @var $this ViewableHTMLNavigation */
	echo $this->getAllHTMLAttributes(true);
?>>
	<?php if ($this->hasItems()){?>
	<ul>
		<?php
			foreach ($this->items as $item){
				$item->view($context, true);
			}
		?>
	</ul>
	<?php }?>
</nav>