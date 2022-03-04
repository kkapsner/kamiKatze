<div class="collection <?php echo $this->html($this->getClass() . " "  . implode(" ", explode("|", $context)));?>"><?php

/** @var DBItemCollection $this */

foreach ($this as $item){
	/** @var DBItem $item */
	$item->view($context, true, $args);
}

?>
</div>