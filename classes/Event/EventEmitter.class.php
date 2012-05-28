<?php

/**
 *
 * @author kkapsner
 */
interface EventEmitter{
	public function on($eventType, $callback);
	public function emit($event);

	public static function onStatic($eventType, $callback);
	public static function emitStatic($event);

	/**
	 *
	 * @return EventEmitter The parent EventEmitter
	 */
	public function getParentEmitter();
}

?>
