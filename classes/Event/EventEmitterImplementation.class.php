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
	 * @param Event $event
	 */
	public function emit(Event $event){
		$eventType = $event->getType();
		$event->setCurrentTarget($this);
		if (array_key_exists($eventType, $this->events)){
			foreach ($this->events[$eventType] as $callback){
				call_user_func($callback, $event);
			}
		}
		elseif ($event instanceof EventError){
			throw $event->getError();
		}
		if (!$event->getPropagationStopped() && $this->getParentEmitter()){
			$this->getParentEmitter()->emit($event);
		}
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
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param Event $event
	 * @todo implement
	 */
	public static function emitStatic(Event $event){

	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @param string $eventType
	 * @param callback $callback
	 * @todo implement
	 */
	public static function onStatic($eventType, $callback){

	}

}

?>
