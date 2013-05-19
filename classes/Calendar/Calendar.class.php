<?php

/*
 * Calendar definition file.
 */

/**
 * Description of Calendar
 *
 * @author kkapsner
 * @property String $version iCalendar Version
 * @property String $prodid identifier for the product that generated the calendar
 * @property String $calscale used calendar scale
 * @property String $method method associated with the calendar (???)
 */
class Calendar extends CalendarObject{

	/**
	 * Constructor of Calendar
	 */
	public function __construct(){
		parent::__construct("VCALENDAR");
		$this->version = "2.0";
		$this->prodid = "-//kamiKatze.kkapsner.de/Calendar v. 0.0.1//DE";
	}

}

?>
