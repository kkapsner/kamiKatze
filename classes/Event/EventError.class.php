<?php
/**
 * EventError definition file
 */

/**
 * Event for an occured error
 *
 * @author Korbinian Kapsner
 * @package Event
 */
class EventError extends Event{
	/**
	 * Exception to the Event.
	 * @var Exception
	 */
	protected $error;

	/**
	 * Constructor of EventError
	 * 
	 * @param string $type
	 * @param EventEmitter $target
	 * @param Exception $error
	 */
	public function __construct($type, EventEmitter $target, Exception $error){
		parent::__construct($type, $target);
		$this->error = $error;
	}

	/**
	 * Returns the Exeption to the event.
	 *
	 * @return Exception
	 */
	public function getError(){
		return $this->error;
	}
}

?>
