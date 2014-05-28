<?php
/**
 * CSVReader definition file
 */

/**
 * Reads CSV files. Accessing data lines is over the ArrayAccess interface.
 *
 * @link http://tools.ietf.org/html/rfc4180
 * @author Korbinian Kapsner
 * @package CSV
 */
class CSVReader{
	/**
	 * The separator between two cells.
	 * @var char
	 */
	public $separator = ",";

	/**
	 * The enclose to enclose cells.
	 * @var string
	 */
	public $enclose = "\"";
	
	/**
	 * If empty lines should be skipped.
	 * @var boolen
	 */
	public $skipEmptyLines = true;

	/**
	 * Parses CSV data to an array.
	 * 
	 * @param type $data
	 */
	public function parse($data, $headerLine = false){
		$lines = $this->splitLines($data);
		if ($headerLine){
			$columnKeys = $this->parseLine(array_shift($lines));
		}
		else {
			$columnKeys = null;
		}
		$rows = array();
		foreach ($lines as $line){
			if (trim($line)){
				$rows[] = $this->parseLine($line, $columnKeys);
			}
		}
		return $rows;
	}
	
	/**
	 * splits the data in data lines.
	 * @param string $data
	 * @return array
	 */
	protected function splitLines($data){
		
		$lines = array();
		$currentLine = "";
		$inStr = false;
		$strlen = strlen($data);
		for ($i = 0; $i < $strlen; $i++){
			$c = $data{$i};
			switch ($c){
				case $this->enclose:
					if ($inStr){
						if ($i + 1 < $strlen && $data{$i + 1} === $this->enclose){
							$i += 1;
							$currentLine .= $this->enclose. $this->enclose;
						}
						else {
							$inStr = false;
							$currentLine .= $this->enclose;
						}
					}
					else {
						$inStr = true;
						$currentLine .= $this->enclose;
					}
					break;
				case "\r":
					if ($i + 1 < $strlen && $data{$i + 1} === "\n"){
						$i++;
						$c .= "\n";
					}
				case "\n":
					if ($inStr){
						$currentLine .= $c;
					}
					else {
						if (
							!$this->skipEmptyLines ||
							!(
								strlen($currentLine) === 0 ||
								preg_match("/^(?:" . preg_quote($this->separator, "/") . ")*$/", $currentLine)
							)
						){
							array_push($lines, $currentLine);
						}
						$currentLine = "";
					}
					break;
				case '\\':
					if ($i + 1 < $strlen){
						$i++;
						$c .= $data{$i};
						$currentLine .= $c;
					}
					break;
				default:
					$currentLine .= $c;
			}
		}
		if (
			!$this->skipEmptyLines ||
			!(
				strlen($currentLine) === 0 ||
				preg_match("/^(?:" . preg_quote($this->separator, "/") . ")*$/", $currentLine)
			)
		){
			array_push($lines, $currentLine);
		}
		
		return $lines;
	}
	
	/**
	 * splits a data in cells
	 * @param string $line
	 * @param null|array $columnKeys optional column keys that will be used as
	 *	keys in the associative array.
	 * @return array
	 */
	protected function parseLine($line, $columnKeys = null){
		$row = array();
			
		$strlen = strlen($line);
		$currentCell = "";
		$columnIdx = 0;
		$inStr = false;
		for ($i = 0; $i < $strlen; $i++){

			$c = $line{$i};
			switch ($c){
				case $this->enclose:
					if ($inStr){
						if ($i + 1 < $strlen && $line{$i + 1} === $this->enclose){
							$i += 1;
							$currentCell .= $this->enclose;
						}
						else {
							$inStr = false;
						}
					}
					else {
						$inStr = true;
					}
					break;
				case '\\':
					if ($i + 1 < $strlen){
						$i++;
						$c .= $line{$i};
						$currentCell .= $c;
					}
					break;
				case $this->separator:
						if (!$inStr){
						if ($columnKeys && array_key_exists($columnIdx, $columnKeys) && $columnKeys[$columnIdx]){
							$row[$columnKeys[$columnIdx]] = $currentCell;
						}
						else {
							$row[$columnIdx] = $currentCell;
						}
						$columnIdx += 1;
						$currentCell = "";
						break;
					}
				default:
					$currentCell .= $c;
			}
		}
		
		if ($columnKeys){
			$row[$columnKeys[$columnIdx]] = $currentCell;
		}
		else {
			$row[$columnIdx] = $currentCell;
		}
		
		return $row;
	}
}

?>
