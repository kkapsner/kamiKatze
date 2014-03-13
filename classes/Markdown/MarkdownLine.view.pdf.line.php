<?php
/*@var $this MarkdownLine*/
/*@var $pdf PDF*/
$pdf = $args;

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
		$pdf->Write(5, substr($this->line, $lastEnd, $match[0][1] - $lastEnd));
		$pdf->Write(5, $match[1][0], $match[2][0]);
		$lastEnd = $match[0][1] + strlen($match[0][0]);
	}
	$pdf->Write(5, substr($this->line, $lastEnd));
}
else {
	$pdf->Write(5, $this->line);
}
?>