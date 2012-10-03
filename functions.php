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
	if (array_key_exists($key, $array)){
		return $array[$key];
	}
	else {
		return $default;
	}
}
?>