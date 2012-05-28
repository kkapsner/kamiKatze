<?php

/**
 * @author kkapsner
 */
class Event{
	/**
	 * The emitter which fired the event.
	 * @var EventEmitter
	 */
	protected $target = NULL;
	/**
	 * @return EventEmitter The EventEmitter which fired the event.
	 */
	public function getTarget(){
		return $this->target;
	}
	
	/**
	 * The EventEmitter that the Event-callback is registered in.
	 * @var EventEmitter
	 */
	protected $currentTarget = NULL;
	/**
	 * @param EventEmitter $currentTarget The EventEmitter that the Event-callback is registered in.
	 */
	public function setCurrentTarget(EventEmitter $currentTarget){
		$this->currentTarget = $currentTarget;
	}
	/**
	 * @return EventEmitter The EventEmitter that the Event-callback is registered in.
	 */
	public function getCurrentTarget(){
		return $this->currentTarget;
	}

	/**
	 * The event type.
	 * @var string
	 */
	protected $type = NULL;
	/**
	 * @return string The event-type.
	 */
	public function getType(){
		return $this->type;
	}

	public function __construct($type, EventEmitter $target){
		$this->type = $type;
		$this->target = $target;
	}
	
	/**
	 * If the default action should be prevented.
	 * @var bool
	 */
	private $defaultPrevented = false;
	/**
	 * Prevents the default action.
	 */
	public function preventDefault(){
		$this->defaultPrevented = true;
	}
	/**
	 *
	 * @return bool if the default action is prevented.
	 */
	public function getDefaultPrevented(){
		return $this->defaultPrevented;
	}
	
	/**
	 * If the propagation should be stopped.
	 * @var bool
	 */
	private $propagationStopped = false;
	/**
	 * Stops the propagation
	 */
	public function stopPropagation(){
		$this->propagationStopped = true;
	}
	public function getPropagationStopped(){
		return $this->propagationStopped;
	}

	/**
	 * Array of further properties of the event.
	 * @var array
	 */
	private $properties = array();
	/**
	 * Sets a named property.
	 * @param string $name The property name
	 * @param mixed $value The new property value
	 */
	public function setProperty($name, $value){
		$this->properties[$name] = $value;
	}
	/**
	 * Gets a named property.
	 * @param string $name The property name
	 * @return mixed
	 */
	public function getProperty($name){
		return array_key_exists($name, $this->properties)?
			$this->properties[$name]:
			NULL;
	}
}
?>
