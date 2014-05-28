<?php
/**
 * CSVWriter definition file
 */

/**
 * Writes CSV to files, streams or output.
 *
 * @link http://tools.ietf.org/html/rfc4180
 * @author Korbinian kapsner
 * @package CSV
 */
class CSVWriter{
	/**
	 * The separator between two cells.
	 * @var char
	 */
	public $separator = ",";

	/**
	 * The separator between two records/lines.
	 * @var string
	 */
	public $newLine = "\r\n";

	/**
	 * The enclose to enclose cells.
	 * @var string
	 */
	public $enclose = "\"";

	/**
	 * Processes the fields value so that it can be inserted in the CSV.
	 *
	 * @param string $fieldValue
	 * @return string
	 */
	protected function formatField($fieldValue){
		if (preg_match("/" . preg_quote($this->separator, "/") . "|" . preg_quote($this->enclose, "/") . "|[\n\r]/", $fieldValue)){
			return $this->enclose . str_replace($this->enclose, $this->enclose . $this->enclose, $fieldValue) . $this->enclose;
		}
		else {
			return $fieldValue;
		}
	}

	/**
	 * Writes data to a string which is then returned.
	 *
	 * @param array $data
	 * @return string
	 */
	public function writeToString($data){
		$fh = fopen("php://temp", "w");
		$this->writeToStream($fh, $data);
		rewind($fh);
		$ret = stream_get_contents($fh);
		fclose($fh);
		return $ret;
	}

	/**
	 * Writes data direct to the output.
	 *
	 * @param array $data
	 * @return int Number of written bytes.
	 */
	public function writeToOutput($data){
		return $this->writeToFile("php://output", $data);
	}

	/**
	 * Writes data in a file.
	 *
	 * @param string $filename the filename to write to
	 * @param array $data
	 * @return int Number of written bytes.
	 */
	public function writeToFile($filename, $data){
		$fh = fopen($filename, "w");
		$ret = $this->writeToStream($fh, $data);
		fclose($fh);
		return $ret;
	}

	/**
	 * Writes data to a stream.
	 *
	 * @param resource $handle The file handle or socket handle to write to.
	 * @param array $data
	 * @return int Number of written bytes.
	 */
	public function writeToStream($handle, $data){
		$dataSize = count($data);
		$ret = 0;
		foreach ($data as $line){
			$dataSize--;
			$lineLength = count($line);
			foreach ($line as $cell){
				$lineLength--;
				$ret += fwrite($handle, $this->formatField($cell));
				if ($lineLength){
					$ret += fwrite($handle, $this->separator);
				}
			}
			if ($dataSize){
				$ret += fwrite($handle, $this->newLine);
			}
		}
		return $ret;
	}
}

?>
