<?php
/**
 * declares some globale funktions
 * 
 */

/**
 * Checkes if a key exists in an array. If it exists the value is returned. Else it returns the value given in $default or false.
 * @param mixed $key
 * @param array $array
 * @param mixed $default
 * @return mixed
 */
function array_read_key($key, $array, $default = false){
	if (is_array($array) && array_key_exists($key, $array)){
		return $array[$key];
	}
	else {
		return $default;
	}
}

/**
 * Creates <input type="hidden"> fields to reprocude the $got array.
 * 
 * @param array $got
 * @param string $name
 * @return string
 */
function createHiddenFields($got, $name = ""){
	$ret = "";
	foreach($got as $k => $v){
		if ($name){
			$k = $name . "[" . $k . "]";
		}
		if (is_array($v)){
			$ret .= createHiddenFields($v, true, $k);
		}
		else {
			$ret .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
		}
	}
	return $ret;
}

/**
 * Includes a file and returns the outputed string.
 * 
 * @param string $filename
 * @return boolean|string the output or false on failure.
 */
function include_to_string($filename){
    if (is_file($filename)) {
        ob_start();
        include($filename);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}
?>