<?php
/**
 * Initialion of the autoload process and registration of global functions
 */

/**
 * Import main files.
 */
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "initialise.php");
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Autoload.class.php");
include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "functions.php");

spl_autoload_register(array(Autoload::getInstance(), "load"));

?>