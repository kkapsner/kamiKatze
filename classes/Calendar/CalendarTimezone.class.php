<?php

/*
 * CalendarTimezon declaration file.
 */

/**
 * Description of CalendarTimezone
 *
 * @author kkapsner
 */
class CalendarTimezone extends CalendarObject{
	
	/**
	 * Constructor for CalendarTimezone
	 */
	public function __construct(){
		parent::__construct("VTIMEZONE");
	}

	/**
	 * Adds the german timezone settings to a calendar
	 * @param Calendar $cal
	 */
	public static function addGermanTimezone(Calendar $cal){
		$timezone = new CalendarTimezone();
		$timezone->tzid = "Europe/Berlin";
		
		$daylight = $timezone->addChild("DAYLIGHT");
		$daylight->tzoffsetfrom = "+0100";
		$daylight->tzoffsetto = "+0200";
		$daylight->tzname = "CEST";
		$daylight->dtstart = "19700329T020000";
		$daylight->rrule = "FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3";
		
		$standard = $timezone->addChild("STANDARD");
		$standard->tzoffsetfrom = "+0200";
		$standard->tzoffsetto = "+0100";
		$standard->tzname = "CET";
		$standard->dtstart = "19701025T030000";
		$standard->rrule = "FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10";
		
		$cal->addChild($timezone);
		
		$timezone = new CalendarTimezone();
		$timezone->tzid = "W. Europe Standard Time";
		
		$daylight = $timezone->addChild("DAYLIGHT");
		$daylight->tzoffsetfrom = "+0100";
		$daylight->tzoffsetto = "+0200";
		$daylight->dtstart = "16010101T020000";
		$daylight->rrule = "FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3";
		
		$standard = $timezone->addChild("STANDARD");
		$standard->tzoffsetfrom = "+0200";
		$standard->tzoffsetto = "+0100";
		$standard->dtstart = "16010101T030000";
		$standard->rrule = "FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10";
		
		$cal->addChild($timezone);
	}
}

?>
