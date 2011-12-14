<?php

/**
 * Description of TickTack
 *
 * @author kkapsner
 */
class TickTack{
	public $lastTime;

	public function getTime(){
		return microtime(true);
	}

	public function tick(){
		$this->lastTime = $this->getTime();
	}

	public function tack(){
		echo ($this->getTime() - $this->lastTime) . "\n";
	}
	
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