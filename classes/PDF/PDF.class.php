<?php
require_once(
	dirname(__FILE__) . 
	DIRECTORY_SEPARATOR .
	"FPDF" .
	DIRECTORY_SEPARATOR .
	"FPDF_bookmark.php");

class PDF extends PDF_Bookmark{

	function Write($h, $txt, $link=''){
		$txt = utf8_decode($txt);
		parent::Write($h, $txt, $link);
	}
	
	function CenteredImage($image){
		$size = getImageSize($image);
		$faktor = min(
			($this->w - $this->lMargin - $this->rMargin) / $size[0],
			($this->h - $this->tMargin - $this->bMargin) / $size[1]
		);
		$this->Image(
			$image,
			($this->w - $size[0] * $faktor) / 2,
			($this->h - $size[1] * $faktor) / 2,
			$size[0] * $faktor,
			$size[1] * $faktor
		);
	}
	
	function GetYFromEnd(){
		return $this->h - $this->GetY();
	}
	
	function GetXFromEnd(){
		return $this->w - $this->GetX();
	}
	
	
	function MoveX($x){
		return $this->SetX($this->GetX() + $x);
	}
	
	function MoveY($y){
		return $this->SetY($this->GetY() + $y);
	}
	
	
	function GetPageSize(){
		return array($this->w, $this->h);
	}
	
	function GetWriteablePageSize(){
		return array($this->w - $this->lMargin - $this->rMargin, $this->h - $this->tMargin - $this->bMargin);
	}
	
	
	function GetFontSize(){
		return $this->FontSizePt;
	}
	
	function SetFontSizeInUnit($size){
		return $this->SetFontSize($size * $this->k);
	}
	
	
	function GetUnit(){
		
		if($this->k==1)
			return 'pt';
		elseif(abs($this->k - 72/25.4) < 0.01)
			return 'mm';
		elseif(abs($this->k - 72/2.54) < 0.01)
			return 'cm';
		elseif(abs($this->k - 72) < 0.01)
			return 'in';
		else
			$this->Error('Unit cannot be spezified: scale factor = ' . $this->k);
	}
	
	function SetUnit($unit){
		$k = $this->k;
		
		if($unit=='pt')
			$this->k=1;
		elseif($unit=='mm')
			$this->k=72/25.4;
		elseif($unit=='cm')
			$this->k=72/2.54;
		elseif($unit=='in')
			$this->k=72;
		else
			$this->Error('Incorrect unit: '.$unit);
			
		$changes = array("x", "y", "w", "h", "LineWidth", "FontSize", "tMargin", "rMargin", "bMargin", "lMargin", "cMargin", "lasth", "ws", "PageBreakTrigger");
		$changeArrays = array("DefPageFormat", "CurPageFormat", "PageSizes");
		
		foreach($changes as $c){
			$this->{$c} *= $k;
		}
		foreach($changeArrays as $c){
			for ($i = 0; $i < count($this->{$c}); $i++){
				$this->{$c}[$i] *= $k;
			}
		}
		
		foreach($changes as $c){
			$this->{$c} /=$this->k;
		}
		foreach($changeArrays as $c){
			for ($i = 0; $i < count($this->{$c}); $i++){
				$this->{$c}[$i] /= $this->k;
			}
		}
		
		return $this;
	}
}

?>