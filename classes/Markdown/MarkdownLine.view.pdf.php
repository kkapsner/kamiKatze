<?php
/*@var $this MarkdownLine*/
/*@var $pdf PDF*/
$pdf = $args;

if ($this->isHeading){
	$pdf->SetFont("", "B", 17 - $this->headingLevel);
	$pdf->Ln();
	$this->view("pdf.line", false, $args);
	$pdf->Ln();
	$pdf->SetFont("", "", 12);
}
elseif ($this->isList){
	$pdf->Ln();
	$pdf->Write(5, '* ');
	$this->view("pdf.line", false, $args);
}
else {
	if ($this->prev instanceof MarkdownLine){
		if (!$this->prev->isHeading){
			if ($this->newParagraph){
				$pdf->Ln();
			}
			elseif ($this->prev->newLine){
				$pdf->Ln();
			}
		}
	}
	$this->view("pdf.line", false, $args);
}
?>
