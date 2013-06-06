<?php
/**
 * @Date: 04-2010
 * @author: Korbinian Kapsner
 * uncommercial use permitted
 * this Header muststay intact for legal use
 * Thanks to:
 *	inspired by http://www.thestyleworks.de/tut-art/css-constants.shtml and http://www.shauninman.com/plete/2005/08/css-constants.php
*/

class CSSParser{
	public
		$constants,
		$variables,
		$displayParserActions = true
	;
	
	private
		$recursion = 0,
		$version = 1.1,
		$lastModified,
		$lastContent = ""
	;
	
	static private
		$strRegExp = '\'(?:\\\\.|[^\'])+\'|"(?:\\\\.|[^\"])+"'
	;
	
	public function __construct(array $const = array(), $lastModified = false){
		$this->constants = $const;
		$this->lastModified = $lastModified;
	}
	
	public function output($minimize = false, $toReturn = false){
		$this->sendHeaders();
		if ($minimize){
			$ret = $this->minimizeCSS($this->lastContent);
		}
		else {
			$ret = $this->lastContent;
		}
		
		if ($toReturn) return $ret;
		else echo $ret;
	}
	
	public function sendHeaders($dieOnNotModified = true){
		header("Content-Type: text/css");
		if ($this->lastModified){
			header("Last-Modified: " . date("r", $this->lastModified));
			header('ETag: "' . md5($this->lastModified) . '"');
			if (array_key_exists("HTTP_IF_MODIFIED_SINCE", $_SERVER) && $this->lastModified <= strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"])){
				header($_SERVER["SERVER_PROTOCOL"] . " 304 Not Modified");
				if ($dieOnNotModified) die();
			}
		}
		header("Cache-Control: public");
	}
	
	public function parse($content){
		$content = $this->resolveConstructs($content);
		$content = $this->parseConstants($content);
		
		$content = preg_replace_callback('/@parser\s+include\s*\(\s*(' .
											'[^\(\)\'"]+' . '|' . 
											self::$strRegExp . 
										')\s*\)\s*;/', array($this, "parseFileRegExpWrapper"), $content);
		
		$content = $this->resolveScope($content);
		
		foreach ($this->constants as $name => $value){
			$content = preg_replace("/(?<!\\.|#|')\\b" . preg_quote($name, "/") . "\\b/", $value, $content);
		}
		
		if ($this->displayParserActions && $this->recursion == 0){
			$content = $this->displayHeader() . "\n\n" . $this->displayConstants() . "\n\n" . $content;
		}
		
		$this->lastContent = $content;
		return $content;
	}
	
	public function minimizeCSS($str){
		return preg_replace('/' . '(' . self::$strRegExp . ')|'
								. '(?<=[{};:,])[\n\r\s]+' . '|'
								. '[\n\r\s]+(?=[{};:,])' . '|'
								. '[\r\n\s]*\/\*[\s\S]*?\*\/[\r\n\s]*'
							. '/', "$1", $str);
	}
	
	public function parseFile($filename){
		if (!is_file($filename)){
			return $this->comment($filename . " not found!");
		}
		$content = file_get_contents($filename);
		$this->lastModified = max($this->lastModified, fileMTime($filename));
		$cwd = getcwd();
		chdir(dirname($filename));
		$ret = $this->parse($content);
		chdir($cwd);
		return $ret;
	}
	
	private function parseFileRegExpWrapper($match){
		$this->recursion++;
		$filename = preg_replace("/^['\"]|['\"]$/", "", $match[1]);
		$ret = $this->parseFile($filename);
		$this->recursion--;
		if ($this->displayParserActions) return $this->comment(" --- start parser-include from file " . $filename . " --- ") .
												$ret . 
												$this->comment(" --- end parser-include --- ");
		return $ret;
	}
	
	private function parseConstants($content){
		return preg_replace_callback('/@parser\s+constants\s{((?:[^{}"\']|' . self::$strRegExp . ')+)}\s*/', array($this, "parseConstantsRegExpWrapper"), $content);
	}
	
	private function parseConstantsRegExpWrapper($match){
		switch(count($match)){
			// first call
			case 2:
				return $this->comment(
						preg_replace_callback("/[\n\r\s]*" .
											"([\w\d\-]+)" .
											"\s*:\s*" .
											"(" .
												"(?:" .
													"[^;'\"]" . "|" .
													self::$strRegExp .
												")+" .
											")" .
											";[\n\r\s]*/", array($this, __METHOD__), $match[1])
						);
				break;
			// called by itselve
			case 3:
				if (!array_key_exists($match[1], $this->constants)){
					$this->constants[$match[1]] = $match[2];
				}
				else {
					return "cannot redeclare constant '" . $match[1] . "' (try to set it to '" . $match[2] . "')\n";
				}
				break;
		}
		return "";
	}
	
	private function resolveConstructs($content){
		return $content;
	}
	
	private function parseForRegExp($match){
		
	}
	
	private function resolveScope($content){
		return preg_replace_callback(
			'/(?:\r?\n)?[ \t]*@parser\s+scope\s+([^{}]+)({(?:[^{}"\']+|' . self::$strRegExp . '|(?2))*})[ \t]*(?:\r?\n)?/',
			array($this, "parseScopeRegExpWrapper"),
			$content
		);
	}
	
	private function parseScopeRegExpWrapper($match){
		$name = preg_replace('/\s+$/', "", $match[1]);
		#if ($name{strlen($name) - 1} == "|") $name = substr($name, 0, -1);
		#else $name .= " ";
		$escapedName = str_replace(array('\\', '"'), array('\\\\', '\\"'), $name);
		$content = preg_replace_callback(
			'/[ \t]*([^{}\n\r,]*)({(?:[^{}"\']+|' . self::$strRegExp . ')*}|,\s*)/',
			create_function('$match', '
				if (strlen ($match[1]) == 0 || $match[1]{0} == "|") $match[1] = substr($match[1], 1);
				else $match[1] = " " . $match[1];
				return "' . $escapedName . '" . $match[1] . $match[2];
			'),
			$this->resolveScope(
				preg_replace('/^{|[ \t]*}$/', "", $match[2])
			)
		);
		return $content;
	}
	
	private function displayConstants(){
		$ret = " --- parser constants ---\n";
		foreach ($this->constants as $name => $value){
			$ret .= "\t" . $name . ": " . $value . ";\n";
		}
		return $this->comment($ret);
	}
	
	private function displayHeader(){
		return $this->comment(
" --- made by CSSParser ---
	Author: Korbinian Kapsner
	Version: " . $this->version);
	}
	
	private function comment($str){
		if (!$str) return "";
		elseif (strpos($str, "\n") === false) return "\n/*" . $str . "*/\n";
		else return "\n/*********************\n" . preg_replace('/^\r?\n|\r?\n$/', "", $str) . "\n *********************/\n";
	}
}

?>