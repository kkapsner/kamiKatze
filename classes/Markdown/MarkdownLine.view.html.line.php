<?php
/** @var MarkdownLine $this*/

if (
	preg_match_all(
		'@\\[([^\\[\\]]+)\\]\\(([^\\(\\)\"]+)(?:\\s+"([^"]+)")?\\)@',
		$this->line,
		$matches,
		PREG_SET_ORDER | PREG_OFFSET_CAPTURE
	)
){
	$lastEnd = 0;
	foreach ($matches as $match){
		echo $this->html(substr($this->line, $lastEnd, $match[0][1] - $lastEnd));
		echo '<a href="' .
				$this->html($match[2][0]) . 
			'" title="' .
				(count($match) >= 4? $this->html($match[3][0]): "") .
			'">' .
				$this->html($match[1][0]) .
			'</a>';
		$lastEnd = $match[0][1] + strlen($match[0][0]);
	}
	echo $this->html(substr($this->line, $lastEnd));
}
else {
	echo $this->html($this->line);
}
?>