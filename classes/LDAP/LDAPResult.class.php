<?php
/**
 * DLAPResult definition file
 */

/**
 * Class to be returned by {@see LDAP::search} to represent the result.
 *
 * @author Korbinian Kapsner
 * @package LDAP
 * @todo implement iterator
 * @todo references? like in ldap_first_reference
 */
class LDAPResult extends LDAPResourceContainer implements Countable, Iterator{
	/**
	 * The LDAP connection that returned this result.
	 * @var LDAP
	 */
	protected $connection;

	/**
	 * The current result entry
	 * @var LDAPResultEntry
	 */
	private $currentEntry = false;
	
	/**
	 * The current result entry number
	 * @var int
	 */
	private $currentEntryNumber = 0;

	/**
	 * Constructor for LDAPResult
	 * 
	 * @param LDAP $connection
	 * @param resource $resultResource
	 */
	public function __construct(LDAP $connection, $resultResource){
		$this->connection = $connection;
		$this->resource = $resultResource;
	}

	/**
	 * Destructor for LDAPResult
	 */
	public function __destruct(){
		$this->free();
	}

	/**
	 * Frees up the memory used by this result.
	 *
	 * Better destruct the instance than use this method.
	 * @return boolean If the free operation was successful
	 */
	public function free(){
		return ldap_free_result($this->resource);
	}

	/**
	 * Returns the first entry of the result
	 * 
	 * @return LDAPResultEntry|false The entry on success or false on failure.
	 */
	public function getFirstEntry(){
		$entryResource = ldap_first_entry($this->connection->resource, $this->resource);
		if ($entryResource !== false){
			return new LDAPResultEntry($this->connection, $this, $entryResource);
		}
		else {
			return false;
		}
	}

	/**
	 * Reads all entries into one big multidimenisional array.
	 *
	 * @return array|false The array or false on failure.
	 */
	public function getEntries(){
		return ldap_get_entries($this->connection->resource, $this->resource);
	}

	/**
	 * Sorts the result by a specific field.
	 * 
	 * @param string $by the field to sort by
	 */
	public function sort($by){
		ldap_sort($this->connection->resource, $this->resource, $by);
	}

	# Countable interface
	/**
	 * {@inheritdoc}
	 * 
	 * @return int
	 */
	public function count(){
		return ldap_count_entries($this->connection->resource, $this->resource);
	}

	# Iterator interface
	/**
	 * Returns the current entry.
	 *
	 * @return LDAPResultEntry
	 */
	public function current(){
		return $this->currentEntry;
	}

	/**
	 * Returns the current entry number.
	 * 
	 * @return int
	 */
	public function key(){
		return $this->currentEntryNumber;
	}

	/**
	 * {@inheritdoc}
	 */
	public function next(){
		if ($this->valid()){
			$this->currentEntry = $this->currentEntry->nextEntry();
			$this->currentEntryNumber++;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function rewind(){
		$this->currentEntry = $this->getFirstEntry();
		$this->currentEntryNumber = 0;
	}

	/**
	 * {@inheritdoc]
	 * 
	 * @return boolean
	 */
	public function valid(){
		return $this->currentEntry !== false;
	}

}

?>
