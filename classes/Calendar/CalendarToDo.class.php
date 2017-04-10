<?php

/*
 * CalendarToDo declaration file.
 */

/**
 * A todo in a calendar.
 * 
 * @author kkapsner
 * @property String $dtstart start of the todo
 * @property String $due due of the todo
 */
class CalendarToDo extends CalendarObject{
	
	/**
	 * Constructor of CalendarEvent
	 */
	public function __construct(){
		parent::__construct("VTODO");
	}
}