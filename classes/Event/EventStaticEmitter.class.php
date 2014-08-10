<?php
/**
 * EventStaticEmitter definition file
 */

/**
 * Interface for an event emitter for static events.
 *
 * @author Korbinian Kapsner
 * @package Event
 */
interface EventStaticEmitter{

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
	 * @param Event|String $event the event type
	 */
	public static function emitStatic($event);

	/**
	 * Getter for a parent emitter if a emitter chain is present
	 * @return EventStaticEmitter The parent EventStaticEmitter
	 */
	public static function getParentStaticEmitter();

}

?>
