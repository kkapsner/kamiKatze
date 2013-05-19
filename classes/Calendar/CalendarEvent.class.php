<?php

/*
 * CalendarEvent declaration file.
 */

/**
 * An event in a calendar.
 * 
 * @author kkapsner
 * @property String $dtstart start of the event
 */
class CalendarEvent extends CalendarObject{
	
	/**
	 * Constructor of CalendarEvent
	 */
	public function __construct(){
		parent::__construct("VEVENT");
	}
}

?>
