<?php

include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "Autoload.class.php");

spl_autoload_register(array(Autoload::getInstance(), "load"));

?>