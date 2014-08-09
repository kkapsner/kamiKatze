<?php
/**
 * EventEmitterImplementation definition file
 */

/**
 * concrete implementation of EventEmitter
 *
 * @author Korbinian Kapsner
 * @package Event
 */
class EventEmitterImplementation implements EventEmitter{
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
			$event = new Event($event, $this);
		}
		$event->setCurrentTarget($this);
		
		$eventType = "";
		foreach (explode(".", $event->getType()) as $part){
			$eventType .= $part;
			if (array_key_exists($eventType, $this->events)){
				foreach ($this->events[$eventType] as $callback){
					call_user_func($callback, $event);
				}
			}
			$eventType .= ".";
		}
		
		if (!$event->getPropagationStopped() && $this->getParentEmitter()){
			$this->getParentEmitter()->emit($event);
		}
		
		return $event;
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @return EventEmitter
	 */
	public function getParentEmitter(){
		return null;
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
}

?>
