<?php
/** @var LDAPUser $this */
$displayName = $this->displayName;
if ($displayName){
	echo $this->html($displayName);
}
else {
	echo $this->html($this->cn);
}
?>