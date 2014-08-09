<?php
/**
 * EventEmitter definition file
 */

/**
 * Interface for an event emitter.
 *
 * @author Korbinian Kapsner
 * @package Event
 */
interface EventEmitter{
	/**
	 * registers a callback to a specific event type
	 *
	 * @param string $eventType the event type
	 * @param callback $callback the callback to be invoced
	 */
	public function on($eventType, $callback);

	/**
	 * fires a event of a specific type.
	 *
	 * @param Event|String $event the event
	 * @return Event the emitted event
	 */
	public function emit($event);

	/**
	 * Getter for a parent emitter if a emitter chain is present
	 * @return EventEmitter The parent EventEmitter
	 */
	public function getParentEmitter();

}

?>
