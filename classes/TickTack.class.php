<?php
/**
 * TickTack definition file
 */

/**
 * A timing class
 *
 * @author Korbinian Kapsner
 */
class TickTack{
	/**
	 * timestamp of last TickTack::tick() call.
	 * @var int
	 */
	public $lastTime;

	/**
	 * Returns current time stamp.
	 * 
	 * @return int
	 */
	public function getTime(){
		return microtime(true);
	}

	/**
	 * Call @see TickTack::tick() first to start the timing measurement.
	 */
	public function tick(){
		$this->lastTime = $this->getTime();
	}

	/**
	 * Second part of the timing. Will output the time difference between last {@see TickTack::tick()} and TickTack::tack() in micro seconds.
	 *
	 * @return int the time difference in micro seconds
	 */
	public function tack(){
		$diff = $this->getTime() - $this->lastTime;
		echo $diff . "\n";
		return $diff;
	}
	
	/**
	 * Compares to callback function and outputs the different performances.
	 * 
	 * @param callback $callback1 first function to compare
	 * @param callback $callback2 seconde function to compare
	 * @param int $times how often each function should be called.
	 * @param array $arguments1 arguments for $callback1
	 * @param array $arguments2 arguments for $callback2
	 * @todo better output
	 */
	public function compare($callback1, $callback2, $times, array $arguments1 = NULL, array $arguments2 = NULL){
		if ($arguments1 === NULL){
			$arguments1 = array();
		}
		if ($arguments2 === NULL){
			$arguments2 = $arguments1;
		}
		$this->tick();
		for ($i = 0; $i < $times; $i++){
			call_user_func_array($callback1, $arguments1);
		}
		echo "callback1: ";
		$this->tack();
		$this->tick();
		for ($i = 0; $i < $times; $i++){
			call_user_func_array($callback2, $arguments2);
		}
		echo "callback2: ";
		$this->tack();
	}
}

?>