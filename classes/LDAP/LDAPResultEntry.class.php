<?php
/**
 * LDAPResultEntry definition file
 */

/**
 * Class to represent singe result entries of a LDAP search.
 *
 * @author Korbinian Kapsner
 * @package LDAP
 */
class LDAPResultEntry extends LDAPResourceContainer implements Iterator{
	/**
	 * LDAP connection that returned this result entry
	 * @var LDAP
	 */
	protected $connection;

	/**
	 * LDAP result that contains the entry
	 * @var LDAPResult
	 */
	protected $result;
	
	/**
	 * The current attribute
	 * @var string
	 */
	private $currentAttribute = false;

	/**
	 * Constructor of LDAPResultEntry
	 * 
	 * @param LDAP $connection
	 * @param LDAPResult $result
	 * @param type $entryResource
	 */
	function __construct(LDAP $connection, LDAPResult $result, $entryResource){
		$this->connection = $connection;
		$this->result = $result;
		$this->resource = $entryResource;
	}

	/**
	 * Magic function __get(). The entries attribute values can be accessed over it.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	function __get($name){
		switch ($name){
			case "dn":
				return ldap_get_dn($this->connection->resource, $this->resource);
			case "attributes":
				return ldap_get_attributes($this->connection->resource, $this->resource);
			default:
				$values = @ldap_get_values($this->connection->resource, $this->resource, $name);
				if ($values !== false){
					return $values;
				}
				else {
					return array("count" => 0);
				}
		}
		throw new InvalidArgumentException("Property " . $name . " not found.");
	}

	/**
	 * Ready binary data from the entry.
	 * 
	 * @param string $name The attributes name
	 * @return mixed
	 */
	public function getBinary($name){
		return ldap_get_values_len($this->connection->resource, $this->resource, $name);
	}

	/**
	 * Returns the next entry in the search.
	 *
	 * @return LDAPResultEntry|false the next entry an success or false on failure.
	 */
	public function nextEntry(){
		$entryResource = ldap_next_entry($this->connection->resource, $this->resource);
		if ($entryResource !== false){
			return new LDAPResultEntry($this->connection, $this->result, $entryResource);
		}
		else {
			return false;
		}
	}

	/**
	 * Deletes the entry from the LDAP.
	 *
	 * After deleting the result and entry instances should not be used any more.
	 * @return boolean true on success false on failure.
	 */
	public function delete(){
		return ldap_delete($this->connection, $this->dn);
	}
	
	/**
	 * Compares the value of attribute with a proivded value.
	 * 
	 * @param string $attribute the attribute to compare
	 * @param string $value the value to compare with
	 * @return boolean|-1 true on match, false on no match and -1 on error.
	 */
	public function compare($attribute, $value){
		return ldap_compare($this->connection, $this->dn, $attribute, $value);
	}

	# Iterator interface
	
	/**
	 * Returns the value of the current attribute.
	 * 
	 * @return string
	 */
	public function current(){
		return $this->__get($this->currentAttribute);
	}

	/**
	 * Returns the current attribute.
	 *
	 * @return string
	 */
	public function key(){
		return $this->currentAttribute;
	}

	/**
	 * Iterates to the next attribute.
	 */
	public function next(){
		$this->currentAttribute = ldap_next_attribute($this->connection->resource, $this->resource);
		$this->currentAttributeNumber++;
	}

	/**
	 * Moves iterator to first attribute
	 */
	public function rewind(){
		$this->currentAttribute = ldap_first_attribute($this->connection->resource, $this->resource);
		$this->currentAttributeNumber = 0;
	}

	/**
	 * Checks if the iterator is in a valid state.
	 * 
	 * @return boolean
	 */
	public function valid(){
		return $this->currentAttribute !== false;
	}

}

?>