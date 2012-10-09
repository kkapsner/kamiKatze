<?php
/**
 * LDAP definition file
 */

/**
 * OO wrapper for the PHP ldap_ functions. Provides also some additional functionality.
 *
 * @author Korbinian Kapsner
 * @package LDAP
 * @todo extend functionality
 */
class LDAP extends LDAPResourceContainer{
	/**
	 * Search scope base. Only one entry returned.
	 */
	const SCOPE_BASE = 0;
	/**
	 * Search scope one level. The search is only performed on the level of the current dn.
	 */
	const SCOPE_ONE_LEVEL = 1;
	/**
	 * Search scope subtree. The search is performed on the complete subtree under the current dn.
	 */
	const SCOPE_SUBTREE = 2;

	const DEREF_NEVER = LDAP_DEREF_NEVER;
	const DEREF_SEARCHING = LDAP_DEREF_SEARCHING;
	const DEREF_FINDING = LDAP_DEREF_FINDING;
	const DEREF_ALWAYS = LDAP_DEREF_ALWAYS;
	
	/**
	 * Time limit (in seconds) for each search.
	 * Zero means no (client) time limit. Server limits can not be changed.
	 * @var int
	 */
	public $timeLimit = 0;

	/**
	 * Size limit of returned search items.
	 * Zero means no (client) limit. Server limits can not be changed.
	 * @var int
	 */
	public $sizeLimit = 0;

	/**
	 * How dereferencing should be handled.
	 * @var int
	 */
	public $dereferencing = LDAP::DEREF_NEVER;

	/**
	 * Array containing the parts of the current working directory
	 * @var string[]
	 */
	protected $cwd = array();

	/**
	 * The hostname of the server.
	 * @var string
	 */
	protected $hostname;

	/**
	 * The LDAP-port on the server.
	 * @var int
	 */
	protected $port;

	/**
	 * Internal flag if the connection is bound.
	 * @var boolean
	 */
	protected $isBound = false;

	/**
	 * Constructor of LDAP. Calls {@see LDAP::connect()}.
	 *
	 * @param string $hostname
	 * @param int $port
	 */
	public function __construct($hostname = null, $port = 389){
		$this->connect($hostname, $port);
	}


	/**
	 * Connects to a LDAP server
	 *
	 * @param string $hostname the server path
	 * @param int $port the LDAP port on the server
	 * @return boolean if the connect was successful
	 */
	public function connect($hostname = null, $port = 389){
		if ($this->isConnected()){
			return false;
		}
		
		$this->hostname = $hostname;
		$this->port = $port;
		$this->resource = ldap_connect($hostname, $port);
		if ($this->isConnected()){
			$this->emit(new Event("connect", $this));
			$this->protocolVersion = 3;
			return true;
		}
		else {
			$this->emit(new EventError("connectError", $this, new LDAPConnectionException()));
			return false;
		}
	}

	/**
	 * Checks if there is a connection to a server.
	 * @return boolean
	 */
	public function isConnected(){
		return $this->resource !== false;
	}

	/**
	 * 
	 * @param string $bindDN
	 * @param string $bindPwd
	 * @return boolean If the binding was successful
	 */
	public function bind($bindDN = null, $bindPwd = null){
		if (@ldap_bind($this->resource, $bindDN, $bindPwd)){
			$this->isBound = true;
			$this->emit(new Event("bind", $this));
			return true;
		}
		else {
			$this->emit(new EventError("bindError", $this, $this->getException()));
			return false;
		}
	}

	/**
	 * Unbind the connection.
	 *
	 * @return boolean If the unbinding was successful.
	 */
	public function unbind(){
		if ($this->isBound()){
			if (ldap_unbind($this->resource)){
				$this->emit(new Event("unbind", $this));
				$this->isBound = false;
				return true;
			}
			else {
				$this->emit(new EventError("unbindError", $this, $this->getException()));
				return false;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Checks if the connection is bound to a user.
	 *
	 * @return boolean If the connection is bound
	 */
	public function isBound(){
		return $this->isBound;
	}

	/**
	 * Getter for the last occured error.
	 * 
	 * @return LDAPException
	 */
	public function getException(){
		return new LDAPException(ldap_error($this->resource), ldap_errno($this->resource));
	}

	/**
	 * Gets the rootDSE from the server.
	 *
	 * @return LDAPResultEntry|false the rootDSE on success or false on failure
	 * @todo implement
	 */
	public function getRootDSE(){
		$search = $this->search("", "objectClass=*", LDAP::SCOPE_BASE);
		if ($search){
			return $search->getFirstEntry();
		}
		else {
			return false;
		}
	}

	/**
	 * Searches in the LDAP.
	 *
	 * @param string $baseDN
	 * @param string $filter
	 * @param int $scope
	 * @param array $attributes
	 * @param boolean $attributesOnly
	 * @return LDAPResult|boolean
	 * @throws InvalidArgumentException
	 */
	public function search($baseDN, $filter, $scope = LDAP::SCOPE_SUBTREE,
		$attributes = array(), $attributesOnly = false
	){
		$baseDN = $this->resolvePath($baseDN);
		switch ($scope){
			case LDAP::SCOPE_BASE:
				$resultResource = ldap_read($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			case LDAP::SCOPE_ONE_LEVEL:
				$resultResource = ldap_list($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			case LDAP::SCOPE_SUBTREE:
				$resultResource = ldap_search($this->resource, $baseDN, $filter, $attributes,
					$attributesOnly, $this->sizeLimit, $this->timeLimit, $this->dereferencing);
				break;
			default:
				throw new InvalidArgumentException("Argument scope must be one of " .
					"LDAP::SCOPE_BASE, LDAP::SCOPE_ONE_LEVEL or LDAP::SCOPE_SUBTREE");
		}
		
		if ($resultResource !== false){
			$this->emit(new Event("search", $this));
			$result = new LDAPResult($this, $resultResource);
			return $result;
		}
		else {
			$this->emit(new EventError("searchError", $this, $this->getException()));
			return false;
		}
	}

	/**
	 * Magic method __get. Used to get connection options.
	 *
	 * @param string $name
	 * @return mixed
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 */
	public function __get($name){
		if ($this->isConnected()){
			$realName = "LDAP_OPT_" . strtoupper(preg_replace("/([a-z])([A-Z])/", "$1_$2", $name));
			if (defined($realName)){
				if (ldap_get_option($this->resource, constant($realName), $value)){
					return $value;
				}
				else {
					$this->emit(new EventError("optionGetError", $this, $this->getException()));
				}
			}
			else {
				throw new InvalidArgumentException("No connection option " . $name . ".");
			}
		}
		else {
			throw new BadMethodCallException("Connection options can only be set after connecting.");
		}
	}
	/**
	 * Magic method __set. Used to set connection options.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @throws InvalidArgumentException
	 * @throws BadMethodCallException
	 */
	public function __set($name, $value){
		if ($this->isConnected()){
			$realName = "LDAP_OPT_" . strtoupper(preg_replace("/([a-z])([A-Z])/", "$1_$2", $name));
			if (defined($realName)){
				if (!ldap_set_option($this->resource, constant($realName), $value)){
					$this->emit(new EventError("optionSetError", $this, $this->getException()));
				}
			}
			else {
				throw new InvalidArgumentException("No connection option " . $name . ".");
			}
		}
		else {
			throw new BadMethodCallException("Connection options can only be set after connecting.");
		}
	}

	/**
	 * Magic function __call. Wraps all not explicit implemented ldap_ functions.
	 *
	 * @param string $name
	 * @param mixed[] $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments){
		if (function_exists("ldap_" . $name)){
			$func = new ReflectionFunction("ldap_" . $name);
			/* @var $parameter ReflectionParameter[] */
			$parameter = $func->getParameters();
			if (preg_match("/^link(|_identifier)$/i", $parameter[0]->name)){
				array_unshift($arguments, $this->resource);
			}
			return $func->invokeArgs($arguments);
		}
		else {
			throw new BadMethodCallException("No method  called " . $name . " found.");
		}
	}

	/**
	 * Changes the LDAP directory.
	 *
	 * @see LDAP::resolvePath()
	 * @param string $path the new path
	 */
	public function cd($path){
		$this->cwd = $this->resolvePath($path, true);
	}

	/**
	 * Returns the current LDAP directory.
	 * 
	 * @return string
	 */
	public function cwd(){
		return implode(",", $this->cwd);
	}

	/**
	 * Resolves a LDAP path.
	 *
	 * @param string $path
	 * @param boolean $arrayReturn
	 * @return string|string[]
	 * @todo document exact behaviour
	 */
	public function resolvePath($path, $arrayReturn = false){
		$pathParts = preg_split("/,+/", $path);
		$len = count($pathParts);
		switch ($pathParts[$len - 1]){
			case "":
				if ($len > 1){
					$hostname = $this->getRootDSE()->dnshostname[0];
					$rootParts = explode(",", "dc=" . str_replace(".", ",dc=", $hostname));
					$pathParts = array_merge($pathParts, $rootParts);
				}
				break;
			case ".":
			case "..":
				$pathParts = array_merge($pathParts, $this->cwd);
				break;
		}

		for($i = 0; $i < count($pathParts); $i++){
			switch ($pathParts[$i]){
				case "":
				case ".":
					array_splice($pathParts, $i, 1);
					$i--;
					break;
				case "..":
					array_splice($pathParts, $i, 2);
					$i--;
					break;
			}
		}

		if ($arrayReturn){
			return $pathParts;
		}
		else {
			return implode(",", $pathParts);
		}
	}
}

?>
