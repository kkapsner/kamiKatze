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
	 * @param Event $event the event
	 */
	public function emit(Event $event);

	/**
	 * registers a callback to a specific static event type
	 *
	 * @param string $eventType the event type
	 * @param callback $callback the callback to be invoced
	 */
	public static function onStatic($eventType, $callback);

	/**
	 * fires a event of a specific static type.
	 *
	 * @param Event $event the event type
	 */
	public static function emitStatic(Event $event);

	/**
	 * Getter for a parent emitter if a emitter chain is present
	 * @return EventEmitter The parent EventEmitter
	 */
	public function getParentEmitter();

}

?>
