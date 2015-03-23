<?php

/**
 * LDAPObject definition file
 */

/**
 * Description of LDAPObject
 *
 * @author kkapsner
 */
class LDAPObject extends LDAPFriends{
	
	/**
	 * LDAP connection that created the object
	 * @var LDAP
	 */
	protected $ldap = null;
	
	
	/**
	 * The DN of the object
	 * @var String
	 */
	protected $dn;
	
	/**
	 * Generic constructor. Use an LDAP instance to create objects.
	 * @param String $dn
	 */
	protected function __construct(LDAP $ldap, $dn){
		$this->ldap = $ldap;
		if (!$ldap->caseSensitive){
			$dn = strtolower($dn);
		}
		$this->dn = $dn;
	}
	
	/**
	 * Cache for attributes
	 * @var Mixed[]
	 */
	protected $attributeCache = array();
	
	/**
	 * Loads attributes from the LDAP into the cache
	 * @param String[] $attributes
	 */
	public function loadAttributes($attributes){
		$result = $this->ldap->search($this->dn, "(objectclass=*)", LDAP::SCOPE_BASE, $attributes);
		if ($result && ($entry = $result->getFirstEntry())){
			foreach ($attributes as $name){
				$this->attributeCache[$name] = $entry->{$name};
			}
		}
		else {
			throw new LDAPException("Error retrieving attributes.");
		}
	}
	
	/**
	 * Getter for the objects attributes.
	 * @param String $name The attributes name.
	 * @return mixed
	 */
	public function getAttribute($name){
		if (!array_key_exists($name, $this->attributeCache)){
			$this->loadAttributes(array($name));
		}
		return $this->attributeCache[$name];
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
	
	// LDAP friends
	
	protected static function createLDAPObject(LDAP $ldap, $type, $dn){
		$type = "LDAP" . ucfirst(strtolower($type));
		return new $type($ldap, $dn);
	}
	protected function getLDAPObject($type, $dn){
		throw new BadFunctionCallException("Call only from LDAP object.");
	}
}

?>
