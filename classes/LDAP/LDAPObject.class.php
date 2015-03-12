<?php

/**
 * LDAPObject definition file
 */

/**
 * Description of LDAPObject
 *
 * @author kkapsner
 */
class LDAPObject extends ViewableHTML{
	
	/**
	 * LDAP connection to be used to get the user information
	 * @var LDAP
	 */
	public static $ldap = null;
	
	/**
	 * All already generated instances
	 * @var LDAPObject[]
	 */
	protected static $instances = array();
	
	/**
	 * Returns the object with the given type and CN.
	 * @param String $type the type of the object
	 * @param String $cn the CN of the object
	 * @return LDAPObject
	 */
	public static function getByCN($type, $cn){
		$dn = self::$ldap->search(",", "(|(cn=$cn)(uid=$cn))", LDAP::SCOPE_SUBTREE);
		if ($dn && ($dn = $dn->getFirstEntry()) && ($dn = $dn->dn)){
			return self::getByDN($type, $dn);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Returns the object with the given type and DN.
	 * @param String $type the type of the object
	 * @param String $dn the DN of the object
	 * @return LDAPObject
	 */
	public static function getByDN($type, $dn){
		if (array_key_exists($dn, self::$instances)){
			return self::$instances[$dn];
		}
		else {
			$type = "LDAP" . $type;
			return new $type($dn);
		}
	}
	
	/**
	 * The DN of the user
	 * @var String
	 */
	protected $dn;
	
	/**
	 * Generic constructor. Use the getBy... functions to get an instance of an LDAPObject
	 * @param String $dn
	 */
	protected function __construct($dn){
		$this->dn = $dn;
		self::$instances[$dn] = $this;
	}
	
	/**
	 * Getter for the objects attributes.
	 * @param String $name The attributes name.
	 * @return mixed
	 */
	public function getAttribute($name){
		$result = self::$ldap->search($this->dn, "(objectclass=*)", LDAP::SCOPE_BASE, array($name));
		$value = $result->getFirstEntry()->{$name};
		return $value;
	}
	
	public function __get($name){
		if ($name === "dn"){
			return $this->dn;
		}
		$value = $this->getAttribute($name);
		if (is_array($value)){
			if ($value["count"]){
				return $value[0];
			}
			else {
				return null;
			}
		}
		else {
			return $value;
		}
	}
}

?>
