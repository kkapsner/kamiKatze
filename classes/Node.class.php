<?php
/**
 * Node definition file
 */

/**
 * Basic class for all types of nodes.
 *
 * @author Korbinian Kapsner
 */
class Node implements Countable, ArrayAccess, SeekableIterator, EventEmitter{
	/**
	 * The root element.
	 * @var Node
	 */
	private $root = NULL;
	
	/**
	 * The parent element.
	 * @var Node
	 */
	private $parent = NULL;

	/**
	 * The children list.
	 * @var array
	 */
	private $children = array();
	
	/**
	 * Searches for a node in the direct children. If the child is found its index is returned.
	 * 
	 * @param Node $child
	 * @return mixed the index of the child of false otherwise.
	 */
	public function getChildIndex(Node $child){
		return array_search($child, $this->children, true);
	}
	
	/**
	 * Returns the first child.
	 * 
	 * @return Node the first child or NULL if there are no children.
	 */
	public function firstChild(){
		if ($this->count()){
			return $this->children[0];
		}
		return NULL;
	}
	
	/**
	 * Returns the last child.
	 * 
	 * @return Node the last child or NULL if there are no children.
	 */
	public function lastChild(){
		if (($l = $this->count()) !== 0){
			return $this->children[$l - 1];
		}
		return NULL;
	}
	
	/**
	 * Returns the previous node in the tree.
	 * 
	 * @return Node the previous node in the parent or NULL if there are no more children or there is no parent.
	 */
	public function previousNode(){
		if ($this->parent){
			$pos = $this->parent->getChildIndex($this);
			if ($pos - 1 >= 0){
				return $this->parent->children[$pos - 1];
			}
		}
		return NULL;
	}
	
	/**
	 * Return the next node in the tree.
	 * 
	 * @return Node the next node in the parent or NULL if there are no more children or there is no parent.
	 */
	public function nextNode(){
		if ($this->parent){
			$pos = $this->parent->getChildIndex($this);
			if ($pos + 1 < $this->parent->count()){
				return $this->parent->children[$pos + 1];
			}
		}
		return NULL;
	}
	
	/**
	 * Getter for the parent node.
	 * 
	 * @return Node the parent node.
	 */
	public function getParent(){
		return $this->parent;
	}
	
	/**
	 * Returns the trees root.
	 * 
	 * @return Node the root node.
	 */
	public function getRoot(){
		if ($this->root === NULL){
			return $this;
		}
		return $this->root;
	}

		
	/**
	 * Appends a node at the end.
	 * 
	 * @param Node $newChild
	 * @return bool if the tag was inserted
	 */
	public function appendChild(Node $newChild){
		$this->insertBefore($newChild);
	}
	
	/**
	 * Checks if the Node contains $otherNode.
	 * 
	 * @param Node $otherNode
	 * @return bool
	 */
	public function contains(Node $otherNode){
		foreach ($this->children as $child){
			/* @var $child Node*/
			if ($child === $otherNode || $child->contains($otherNode)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Inserts a node before a child-node.
	 * 
	 * @param Node $newChild
	 * @param Node $refNode
	 * @return bool if the tag was inserted
	 */
	public function insertBefore(Node $newChild, Node $refNode = NULL){
		if ($this !== $newChild){
			if ($newChild->parent){
				$newChild->parent->removeChild($newChild);
			}

			if (
				$refNode === NULL ||
				($pos = $this->getChildIndex($newChild)) === false
			){
				$this->children[] = $newChild;
			}
			else {
				array_splice($this->children, $pos, 1, $newChild);
			}

			$newChild->parent = $this;
			$newChild->root = $this->getRoot();
			return true;
		}
		return false;
	}
	
	/**
	 * Removes a child-node form the node.
	 * 
	 * @param Node $oldChild
	 * @return bool if the child was found and removed.
	 */
	public function removeChild(Node $oldChild){
		$pos = $this->getChildIndex($oldChild);
		if ($pos !== false){
			array_splice($this->children, $pos, 1);
			$oldChild->parent = NULL;
			$oldChild->root = NULL;
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Replaces one child through another.
	 * 
	 * @param Node $newChild
	 * @param Node $oldChild
	 * @return bool if the child was replaced.
	 */
	public function replaceChild(Node $newChild, Node $oldChild){
		$pos = $this->getChildIndex($oldChild);
		if ($pos !== false){
			if ($newChild->parent){
				$newChild->parent->removeChild($newChild);
			}
			array_splice($this->children, $pos, 1, $newChild);
			$oldChild->parent = NULL;
			$oldChild->root = NULL;
			return true;
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Interface functions
	 */
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param int $offset
	 * @return boolean
	 */
	public function offsetExists($offset){
		return $offset >= 0 && $offset < $this->count();
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param int $offset
	 * @return Node
	 */
	public function offsetGet($offset){
		return $this->children[$offset];
	}
	
	/**
	 * This function only throws an exception. It's here for inheritance issues.
	 * 
	 * @param int $offset
	 * @param mixed $value
	 * @throws BadMethodCallException
	 */
	public function offsetSet($offset, $value){
		throw new BadMethodCallException("Children can not be set over array access. Use insertBefore or replaceChild.");
	}

	/**
	 * This function only throws an exception. It's here for inheritance issues.
	 * 
	 * @param int $offset
	 * @throws BadMethodCallException
	 */
	public function offsetUnset($offset){
		throw new BadMethodCallException("Children can not be removed over array access. Use removeChild instead.");
		
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @return int
	 */
	public function count(){
		return count($this->children);
	}

	/**
	 * Current pointer of the iterator.
	 * @var int
	 */
	private $current = 0;
		
	/**
	 * {@inheritdoc}
	 * 
	 * @return int
	 */
	public function current(){
		return $this->children[$this->current];
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @return int
	 */
	public function key(){
		return $this->current;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function next(){
		$this->current++;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function rewind(){
		$this->current = 0;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param int $position
	 * @throws OutOfBoundsException
	 */
	public function seek($position){
		if (!$this->offsetExists($position)){
			throw new OutOfBoundsException();
		}
		$this->current = $position;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @return  boolean
	 */
	public function valid(){
		return 0 <= $this->current && $this->current < $this->count();
	}

	/**
	 * EventEmitter methods
	 */

	/**
	 * Registration for event callbacks.
	 * @var array
	 */
	private $events = array();
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param Event|String $event
	 */
	public function emit($event){
		if (!($event instanceof Event)){
			$eventType = $event;
			$event = new Event($eventType, $this);
		}
		else {
			$eventType = $event->getType();
		}
		$event->setCurrentTarget($this);
		if (array_key_exists($eventType, $this->events)){
			foreach ($this->events[$eventType] as $callback){
				call_user_func($callback, $event);
			}
		}
		if (!$event->getPropagationStopped()){
			$this->getParentEmitter()->emit($event);
		}
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @return Node
	 */
	public function getParentEmitter(){
		return $this->parent;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param string $eventType
	 * @param callback $callback
	 */
	public function on($eventType, $callback){
		if (!array_key_exists($eventType, $this->events)){
			$this->events[$eventType] = array();
		}
		$this->events[$eventType][] = $callback;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param Event|String $event
	 */
	public static function emitStatic($event){

	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param string $eventType
	 * @param callback $callback
	 */
	public static function onStatic($eventType, $callback){

	}

}

?>
